$(document).ready(function() {
    /* set up the popover content text and activate it*/
    $('#popinfo').attr("data-content","Here is some amazing content. It's very engaging. Right?");
    $('#popinfo').popover();// activate info

    /* set id & course for the POST if they are hidden in fields.yaml
        Add hidden input fields so they will transfer to onUpdate
        if a field is set to hidden: true it does not appear in the form at all

        in Vanilla I only have id hidden
    */

    //$('#Form-outsideTabs').append('<input type="hidden" name="Vanilla[id]" value="'+config.id+'" /> ');
});

function completed(data)
{
    $('#new-session-modal').modal('hide');
    var text= (data!==null) ? data['message'] :"The record has been successfully saved";
    var classStr = data['success']===1?'success':'error';
    $.oc.flashMsg({
        'text': text,
        'class': classStr,
        'interval': 3
    });
    //location.reload();
    history.go(0);
}

function showAttendanceModal()
{
    $("#attendance-content-configuration").on("shown.bs.modal", function () {
        //you may disable fields that should not be edited by professors
        //document.getElementById('yourid').style.display='none';
    }).modal('show');
}

function hideAttendanceModal()
{
    $('#attendance-content-configuration').modal('hide');
}