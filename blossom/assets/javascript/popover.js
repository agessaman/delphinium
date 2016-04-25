$('#popinfo').popover();// activate info
/* set id,course,copy in the POST if they are hidden in fields.yaml
Add hidden input fields so they will transfer to onUpdate
fields.yaml is set to hidden: true do not appear in the form at all

in Vanilla I only have id hidden
*/

$('#Form-outsideTabs').append('<input type="hidden" name="Vanilla[id]" value="'+config.id+'" /> ');
//$('#Form-outsideTabs').append('<input type="hidden" name="Vanilla[course_id]" value="'+config.course_id+'" /> ');
//$('#Form-outsideTabs').append('<input type="hidden" name="Vanilla[copy_id]" value="'+config.copy_id+'" /> ');

function completed(data)
{
/* updated record is returned */
location.reload();
}
//console.log('instance: id='+config.id,config.name,config.custom,config.course_id,config.copy_id);