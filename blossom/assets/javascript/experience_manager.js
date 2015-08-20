/*
 * Idea from http://www.sanwebe.com/2013/03/addremove-input-fields-dynamically-with-jquery
 */
$(document).ready(function() {
    datePicker();
    addFields();
});

function datePicker()
{
    var d = new Date();
    $('.datepicker').datepicker({
        todayHighlight: true,
        clearBtn: true,
        daysOfWeekDisabled: "0,6"
    });
}

function addFields()
{
    var wrapper         = $("#milestones"); //Fields wrapper
    var add_button      = $("#addMilestones"); //Add button ID
    
    var x = 1; //initlal text box count
    $(add_button).click(function(e){ //on add input button click
        e.preventDefault();
            x++; //text box increment
            $(wrapper).append('<div>\n\
                <input class="form-control col-sm-7" type="text" name="mytext[]" placeholder="Enter the name of this milestone"/>\n\
                <input class="form-control col-sm-3" type="text" name="mytext[]" placeholder="Enter the points for this milestone"/>\n\
                <a href="#" class="remove_field">Remove</a></div>'); //add input box
    });
    
    $(wrapper).on("click",".remove_field", function(e){ //user click on remove text
        e.preventDefault(); $(this).parent('div').remove(); x--;
    });
}