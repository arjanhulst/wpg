<?php

namespace App\Form;

use App\Entity\Dealers;
use App\Entity\Vehicles;
use App\Form\EventListener\FormChangeListener;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Showrooms;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class OrderManagementType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('entity_manager');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $showrooms = $options['entity_manager']->getRepository(Showrooms::class)->findAllAllowed();
        $builder->add('showrooms', EntityType::class, [
            'class' => 'App\Entity\Showrooms',
            'placeholder' => 'Showroom',
            'required' => false,
            'label' => 'Showroom',
            'choices' => $showrooms,
            'choice_label' => function ($showroom, $key, $value) {
                return strtoupper($showroom->getShowroomname());
            },
            'choice_attr' => function ($showroom, $key, $value) {
                return ['class' => '' . strtolower($showroom->getShowroomid())];
            }
        ]);
        $builder->add('submit', SubmitType::class, [
            'attr' => ['class' => 'btn btn-primary float-right'],
            'label' => 'Search'
        ]);


        $formModifier = function (FormInterface $form, $data) use ($options) {

            if (count($data) > 0) {
                extract($data);
            }
            if (isset($showrooms)) {
                $vehicles = $options['entity_manager']->getRepository(Vehicles::class)->findBy(['showroomid' => $showrooms]);
            } else {
                $vehicles = [];
            }
            $modelYears = array();
            if (isset($showrooms)) {
                $result = $options['entity_manager']->getRepository(Vehicles::class)->getModelYears($showrooms);

                foreach ($result as $row) {
                    $modelYears[] = $row['year'];
                }
            }
            if (isset($showrooms)) {
                $dealers = $options['entity_manager']->getRepository(Dealers::class)->findForSelect($showrooms);
            } else {
                $dealers = [];
            }

            $modelGroups = [];
            if (isset($showrooms) && isset($modelyear)) {
                $result = $options['entity_manager']->getRepository(Vehicles::class)->getModelGroups($showrooms, $modelyear);
                foreach ($result as $row) {
                    $modelGroups[] = $row['modelgroup'];
                }
            }
            $form->add('modelyear', ChoiceType::class, [
                'empty_data' => '',
                'required' => false,
                'label' => 'Year',
                'placeholder' => 'Select Model Year',
                'choices' => $modelYears,
                'choice_label' => function ($vehicle, $key, $value) {
                    return $vehicle;
                },
                'choice_value' => function ($vehicle) {
                    return $vehicle;
                }
            ]);
            $form->add('modelgroup', ChoiceType::class, [
                'empty_data' => '',
                'required' => false,
                'label' => 'Model',
                'placeholder' => 'Select Model Group',
                'choices' => $modelGroups,
                'choice_label' => function ($modelGroup, $key, $value) {
                    return $modelGroup;
                },
                'choice_value' => function ($modelGroup) {
                    return $modelGroup;
                }
            ]);
            $form->add('dealers', EntityType::class, [
                'class' => 'App\Entity\Dealers',
                'placeholder' => 'Select Dealer',
                'required' => false,
                'label' => 'Dealer',
                'choices' => $dealers,
                'choice_label' => function ($dealer, $key, $value) {
                    return ($dealer->getDealershortname() != "" ? $dealer->getDealershortname() : $dealer->getDealername());
                },
                'choice_attr' => function ($dealer, $key, $value) {
                    return ['class' => '' . strtolower($dealer->getDealerid())];
                }
            ]);
            $form->add('vehicles', EntityType::class, [
                'class' => 'App\Entity\Vehicles',
                'placeholder' => (count($vehicles) > 0 ? 'Select vehicle' : 'Select a showroom first'),
                'required' => false,
                'label' => 'Vehicle',
                'choices' => $vehicles,
                'choice_label' => function ($vehicle, $key, $value) {
                    return strtoupper($vehicle->getMake() . ' ' . $vehicle->getModel());
                },
                'choice_attr' => function ($vehicle, $key, $value) {
                    return ['class' => '' . strtolower($vehicle->getVehicleid())];
                }
            ]);

        };
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $form = $event->getForm();
                $formModifier($form, $event->getData());
            }
        );

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $formModifier($event->getForm(), $event->getData());
            }
        );
    }

}