$('body').on('change', '#order_management_showrooms,#order_management_modelyear,#order_management_modelgroup,#order_management_vehicles,#order_management_dealers', function () {
    //Get form
    let $form = $(this).closest('form');

    // Simulate form data, but only include the selected sport value.
    let data = [];
    $.each($form.serializeArray(), function (index, item) {

        //only push items that we want to the data array.
        if ($(item).attr('name') != 'order_management[_token]' && item.value != '') {
            data.push(item);
        }
    });

    // Submit data via AJAX to the form's action path.
    $.ajax({
        url: $form.attr('action'),
        type: $form.attr('method'),
        data: data,
        beforeSend: function () {
            swal({
                title: "Please wait",
                text: "We are updating the form",
                onOpen: function () {
                    swal.showLoading()
                }
            })
        },
        complete: function () {
            swal.close();
        },
        success: function (html) {
            replaceField('#order_management_vehicles',html);
            replaceField('#order_management_modelyear',html);
            replaceField('#order_management_modelgroup',html);
            replaceField('#order_management_deakers',html);
        }
    });
});

/*
    Replace field with the new field provided by the form submit.
 */
function replaceField(id,html)
{
    let selectPicker =$(id+'.selectpicker');
    selectPicker.selectpicker('destroy');
    $(id).replaceWith(
        // replace with the returned one from the AJAX response.
        $(html).find(id)
    );
    selectPicker.selectpicker();
}
/*
    Enable / disable transfer button and set count
 */
$('.transfer').change(function () {
    let $checked=$('.transfer:checked');
    if($checked.length > 0)
    {
        $('.transfer-button').enable().val('Transfer '+$checked.length+" vehicles");
    } else {
        $('.transfer-button').enable(false).val('Transfer vehicles');
    }
});

$(document).ready(function () {
    //Use different layout for this form
    $('#reportTable').DataTable({
        processing: !0,
        pageLength: -1,
        dom: "<'row'<'col-sm-12 col-md-12'f>>" +
            "<'row'<'col-sm-12'tr>>",
        paging: false,
        lengthChange: false,
        scrollX: true,
        language: {
            search: "Filter: _INPUT_",
            searchPlaceholder: "Filter records",
        }
    });
});