$(function(){

    function setAge(birth, death) {
        var age = 0;
        if (birth && death) {
            if (birth >= 0 && death >= 0) {
                age = death - birth;
            } else if (birth <= 0 && death <= 0) {
                age = Math.abs(death - birth);
            } else {
                // in this case, bith is before 0 and death after 0
                // then calculate by taking account of "year 0"
                age = death + Math.abs(birth);
            }
        }
        $("form #event_duration").val(age);
    }

    $("form .custom_form_olddate").on('change', function(e){
        var birth = parseInt($("form #event_start_year").val());
        var death = parseInt($("form #event_end_year").val());
        if ($('form #event_start_BC').is(':checked')) {
            birth *= -1;
        }
        if ($('form #event_end_BC').is(':checked')) {
            death *= -1;
        }
        setAge(birth, death);
    });

    $("form #age-calculator").on('click', function(e){
        $("form #event_start_year").trigger('change');
    });
    
    // when assign a event to a timeline, the categories outside this timeline are unckecked and hidden,
    // whereas the categories inside this timeline are shown.
    $("form #event_timeline").on('change', function(e){
        var timeline_id = $(this).val();
        $("form #event_categories input:not(.event-category-timeline-" + timeline_id + ")")
            .prop( "checked", false )
            .parent().hide();
        $("form #event_categories input.event-category-timeline-" + timeline_id).parent().show();
    });
    // on from load, fire the event once to hide all categories not owned by the timeline selected
    $("form #event_timeline").trigger('change');

    // Selectize source styling
    $('input#event_source').selectize({
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
});
