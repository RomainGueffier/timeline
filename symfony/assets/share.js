$(function(){

    // to know if every form is not empty, we get the form name
    // for every submit and check if checkbox are empty or not
    // then we enable button if checkbox are filled
    $("form input[type=checkbox]").on("change", function() {
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
    
});