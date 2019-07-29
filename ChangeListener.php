<?php

namespace App\EventListener;

use App\Entity\Changelog;
use App\Entity\Savedvalues;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\ORMException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ChangeListener
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        foreach ($uow->getScheduledEntityUpdates() as $keyEntity => $entity) {
            if (!$entity instanceof Changelog && !$entity instanceof Savedvalues) {
                foreach ($uow->getEntityChangeSet($entity) as $field => $values) {
                    $classMetadata = $em->getClassMetadata(get_class($entity));
                    $changelog = new Changelog();
                    $changelog->setUpdatetime(new \DateTime());
                    $changelog->setUser($this->tokenStorage->getToken()->getUser());
                    $changelog->setTablename($classMetadata->getTableName());
                    $funcName = 'get' . ucfirst($classMetadata->getIdentifier()[0]);
                    $changelog->setKeyvalue($entity->{$funcName}());
                    $changelog->setFieldname($field);
                    $changelog->setNewvalue($values[1]);
                    $changelog->setOldvalue($values[0]);
                    $em->persist($changelog);
                    $uow->computeChangeSet($em->getClassMetadata(Changelog::class), $changelog);
                }
            }
        }
    }
}