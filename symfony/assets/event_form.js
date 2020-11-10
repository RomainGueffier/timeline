$(document).ready(function(e){
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
    $("form .custom_form_olddate").change(function(e){
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
    $("form #age-calculator").click(function(e){
        $("form #event_start_year").change();
    });
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
});
