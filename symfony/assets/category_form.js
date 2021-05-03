/**
 * JS for category form (add and edit)
 */

export function categoryForm() {

    // when assign a category to a timeline, the characters and events outside this timeline are unckecked and hidden,
    // whereas the ones inside this timeline are shown.
    $("form #category_timeline").on('change', function(e){
        var timeline_id = $(this).val()
        // characters
        $("form #category_characters input:not(.category-timeline-" + timeline_id + ")")
            .prop( "checked", false)
            .parent().hide()
        $("form #category_characters input.category-timeline-" + timeline_id).parent().show()
        // events
        $("form #category_events input:not(.category-timeline-" + timeline_id + ")")
            .prop( "checked", false)
            .parent().hide()
        $("form #category_events input.category-timeline-" + timeline_id).parent().show()
    })
    // on from load, fire the event once to hide all characters and events not owned by the timeline selected
    $("form #category_timeline").trigger('change')
}
