jQuery(function($){
    let datepickerFields = lineUserValues.datepicker_fields;
    if(datepickerFields)
    {
        $.each(datepickerFields, function(key,field_data) {
            console.log(field_data.field_name);
            $('#'+field_data.field_name+'-datepicker').datepicker ({
                dateFormat: field_data.date_format,
            })
        })
        
    }

})