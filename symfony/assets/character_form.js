/**
 * JS for character form (add and edit)
 */

export function characterForm() {

    function setAge(birth, death) {
        var age = 0;
        if (birth && death) {
            if (birth >= 0 && death >= 0) {
                age = death - birth;
            } else if (birth <= 0 && death <= 0) {
                age = Math.abs(death - birth);
            } else {
                // in this case, birth is before 0 and death after 0
                // then calculate by taking account of "year 0"
                age = death + Math.abs(birth);
            }
        }
        $("form #character_age").val(age);
    }

    $("form .custom_form_olddate").on('change', function(e){
        var birth = parseInt($("form #character_birth_year").val());
        var death = parseInt($("form #character_death_year").val());
        if ($('form #character_birth_BC').is(':checked')) {
            birth *= -1;
        }
        if ($('form #character_death_BC').is(':checked')) {
            death *= -1;
        }
        setAge(birth, death);
    });

    $("form #age-calculator").on('click', function(e){
        $("form #character_birth_year").trigger('change');
    });

    // when assign a character to a timeline, the categories outside this timeline are unckecked and hidden,
    // whereas the categories inside this timeline are shown.
    $("form #character_timeline").on('change', function(e){
        var timeline_id = $(this).val();
        $("form #character_categories input:not(.character-category-timeline-" + timeline_id + ")")
            .prop( "checked", false )
            .parent().hide();
        $("form #character_categories input.character-category-timeline-" + timeline_id).parent().show();
    });
    // on from load, fire the event once to hide all categories not owned by the timeline selected
    $("form #character_timeline").trigger('change');

    // Selectize source styling
    $('input#character_source').selectize({
        plugins: ['remove_button'],
        delimiter: ',',
        persist: false,
        create: function(input) {
            return {
                value: input,
                text: input
            }
        }
    });
}