$(function(){

    $("form #start").on('change', function(e) {
        $("form #end").attr('min', $(this).val());
    });
    $("form #end").on('change', function(e) {
        $("form #start").attr('max', $(this).val());
    });
    $("#range").on('change', function(e) {
        let range = $(this).val();
        if (range < 1) {
            range = 1;
        }
        const value = range + (range <= 1 ? ' an' : ' ans');
        $(".badge-range").text(value);
    });
    $("#start").on('change', function(e){
        var start = $(this).val();
        var value = "";
        if (start < 0) {
            value = Math.abs(start) + " av.n.è.";
        } else {
            value = start;
        }
        $(".badge-start").text(value);
    });
    $("#end").on('change', function(e) {
        var end = $(this).val();
        var value = "";
        if (end < 0) {
            value = Math.abs(end) + " av.n.è.";
        } else {
            value = end;
        }
        $(".badge-end").text(value);
    });

    // chargement asynchrone des Personnages / évènements selon la frise chronologique choisie
    var timeline_id = $(".timeline-events").attr('data-timeline-id');
    var ratio = $(".timeline-events").attr('data-ratio');
    var start = $(".timeline-events").attr('data-start');
    var end = $(".timeline-events").attr('data-end');

    $.ajax({
        url: '/character/ajax',
        method: "GET",
        data: {
          ratio: ratio, start: start, end: end, timeline_id: timeline_id
        }
    }).then(function(response) {
        $("#character-loader").remove();
        $(".timeline-events").html(response);
        // Launch ajax listener
        $(document).on("click", ".btn-modal", function(e){
            $(e.currentTarget.getAttribute('data-target')).modal('show');
        });
        // Launch dropdown hide listener
        $(document).on("click", ".btn-hide", function(e){
            $(this).parents(".character").hide('slow');
        });
        // Collapse info z-index fix
        $('.character .collapse').on('show.bs.collapse', function () {
            $(this).parent().css('z-index', '2');
        });
        $('.character .collapse').on('hidden.bs.collapse', function () {
            $(this).parent().css('z-index', '1');
        });
  	});
    $.ajax({
        url: '/event/ajax',
        method: "GET",
        data: {
          ratio: ratio, start: start, end: end, timeline_id: timeline_id
        }
    }).then(function(response) {
        $("#event-loader").remove();
        $(response).insertAfter(".timeline-events");
        // Launch ajax listener
        $(document).on("click", ".btn-modal", function(e){
            $(e.currentTarget.getAttribute('data-target')).modal('show');
        });
        // Launch dropdown hide listener
        $(document).on("click", ".btn-hide", function(e){
            $(this).parents(".event, .longevent").hide('slow');
        });
        // Collapse info z-index fix
        $('.event .collapse, .longevent .collapse').on('show.bs.collapse', function () {
            $(this).parent().css('z-index', '2');
        });
        $('.event .collapse, .longevent .collapse').on('hidden.bs.collapse', function () {
            $(this).parent().css('z-index', '0');
        });
  	});
    $.ajax({
        url: '/category/ajax',
        method: "GET",
        data: {
            timeline_id: timeline_id
        }
    }).then(function(response) {
        $("#modal-categories .modal-body").html(response);
        $("#set_filters").on('click', function(e) {
            // hide events and characters with category id
            $("#modal-categories .modal-body input[type=checkbox]:not(:checked)").each(function(e) {
                var className = '.category-' + $(this).attr('category_id');
                $(className).hide();
            });
            // Then show. If an event or character has 2 categories, one hidden, the other visible,
            // this way it will be visible anyway
            $("#modal-categories .modal-body input[type=checkbox]:checked").each(function(e) {
                var className = '.category-' + $(this).attr('category_id');
                $(className).show();
            });
        });
        $("#customSwitchAll").on('click', function(e) {
            if ($(this).is(':checked')) {
                $("#modal-categories .modal-body input.custom-control-input").prop('checked', true);
            } else {
                $("#modal-categories .modal-body input.custom-control-input").prop('checked', false);
            }
        });
  	});
    $("#btn-events-first").on('click', function() {
        $(".timeline-wrapper .character").css('z-index', '0');
        $(".timeline-wrapper .event, .timeline-wrapper .longevent").css('z-index', '1');
    });
    $("#btn-characters-first").on('click', function() {
        $(".timeline-wrapper .character").css('z-index', '1');
        $(".timeline-wrapper .event, .timeline-wrapper .longevent").css('z-index', '0');
    });
    $("#btn-characters-only").on('click', function() {
        $(".timeline-wrapper .character").show('slow');
        $(".timeline-wrapper .event, .timeline-wrapper .longevent").hide('slow');
    });
    $("#btn-events-only").on('click', function() {
        $(".timeline-wrapper .character").hide('slow');
        $(".timeline-wrapper .event, .timeline-wrapper .longevent").show('slow');
    });
    $("#btn-reset").on('click', function() {
        $(".timeline-wrapper .character, .timeline-wrapper .event, .timeline-wrapper .longevent").show();
        $(".timeline-wrapper .character").css('z-index', '1');
        $(".timeline-wrapper .event, .timeline-wrapper .longevent").css('z-index', '0');
    });
});