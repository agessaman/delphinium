
function updateComponent(copy, course_id){

    var copy = document.getElementById('Form-field-Processmaker-copy_name').value;
    var e = document.getElementById("Form-field-Processmaker-process_id");
    var process_id = e.options[e.selectedIndex].value;
    var course_id = document.getElementById('Form-field-Processmaker-course_id').value;

    var pm = {copy_name:copy, process_id:process_id, course_id:course_id};

    $.request('processmaker::onSave', {data: {instance_id: instance.id, obj:pm}},
        function() {
            console.log('got here!');
        });

    $('#modal-configuration').modal('hide');
    //set success message:

}