$(document).ready(function(e){
    $("form #start").change(function(e){
        $("form #end").attr('min', $(this).val());
    });
    $("form #end").change(function(e){
        $("form #start").attr('max', $(this).val());
    });
    $("#range").change(function(e){
        var range = $(this).val();
        var value = "";
        if (range == 0) value = "1 ans";
        if (range == 1) value = "10 ans";
        if (range == 2) value = "100 ans";
        $(".badge-range").text(value);
    });
    $("#start").change(function(e){
        var start = $(this).val();
        var value = "";
        if (start < 0) {
            value = Math.abs(start) + " av.n.è.";
        } else {
            value = start;
        }
        $(".badge-start").text(value);
    });
    $("#end").change(function(e){
        var end = $(this).val();
        var value = "";
        if (end < 0) {
            value = Math.abs(end) + " av.n.è.";
        } else {
            value = end;
        }
        $(".badge-end").text(value);
    });

    // chargement asynchrone des Personnages / évènements
    var ratio = $(".timeline-events").attr('ratio');
    var start = $(".timeline-events").attr('start');
    $.ajax({
    		url: '/character/ajax',
    		method: "GET",
        data: {
          ratio: ratio, start: start
        }
		}).then(function(response) {
        $("#character-loader").remove();
				$(".timeline-events").html(response);
        // Launch ajax listener
        $(document).on("click", ".btn-modal", function(e){
            $(e.currentTarget.getAttribute('data-target')).modal('show');
        });
  	});
    $.ajax({
    		url: '/event/ajax',
    		method: "GET",
        data: {
          ratio: ratio, start: start
        }
		}).then(function(response) {
        $("#event-loader").remove();
				$(response).insertAfter(".timeline-events");
        // Launch ajax listener
        $(document).on("click", ".btn-modal", function(e){
            $(e.currentTarget.getAttribute('data-target')).modal('show');
        });
  	});
    $.ajax({
    		url: '/category/ajax',
    		method: "GET",
        data: {}
		}).then(function(response) {
				$("#modal-categories .modal-body").html(response);
        $("#set_filters").click(function(e) {
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
        $("#customSwitchAll").click(function(e) {
            if ($(this).is(':checked')) {
                $("#modal-categories .modal-body input.custom-control-input").prop('checked', true);
            } else {
                $("#modal-categories .modal-body input.custom-control-input").prop('checked', false);
            }

        });
  	});
    $(function () {
      $('[data-toggle="tooltip"]').tooltip()
    })
});
