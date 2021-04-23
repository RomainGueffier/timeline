$(function(){

    // EXPORT FORM
    // to know if every form is not empty, we get the form name
    // for every submit and check if checkbox are empty or not
    // then we enable button if checkbox are filled
    $("form input[type=checkbox]").on('change', function() {
        // retrieve submit's form name
        let formName = $(this).closest("form").attr('name');
        // retrieve button element
        let submit = $("form[name=" + formName + "]").find('button[type=submit]');
        // retrieve all cheched checkbox of a form
        let checkboxes = $("form[name=" + formName + "]").find('input[type=checkbox]').is(":checked");
        // enable or disable button following checkbox values
        $(submit).prop("disabled", !checkboxes);
    });
    $("form input[type=checkbox]").trigger('change');

    // IMPORT FORM
    $("form #import_entity").on('change', function() {
        let entityType = $(this).val();
        // disable export_all option for some entities not compatible with this option
        if (!['timelines','categories'].includes(entityType)) {
            $("form #import_type").parent().hide();
            $("form #import_type").find("option[value=export_all]").prop('disabled', 'disabled');
            $("form #import_type").val('export');
        } else {
            $("form #import_type").parent().show();
            $("form #import_type").find("option[value=export_all]").removeAttr('disabled');
        }

        // show or hide timeline association if entities are not a timeline
        if (entityType === 'timelines') {
            $("form #import_timeline").parent().hide();
        } else {
            $("form #import_timeline").parent().show();
        }
    });
    $("form #import_entity").trigger('change');
    
});