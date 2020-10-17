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
        $("form #character_age").val(age);
    }
    $("form .character_form_birthdate").change(function(e){
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
    $("form #character-age-calculator").click(function(e){
        $("form #character_birth_year").change();
    });
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
});
