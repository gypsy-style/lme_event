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

    // 画像
    let frame;
    $('#upload_event_image').on('click', function (e) {
        e.preventDefault();
        if (frame) {
            frame.open();
            return;
        }
        frame = wp.media({
            title: '画像を選択',
            button: { text: '画像を使用' },
            multiple: false
        });
        frame.on('select', function () {
            const attachment = frame.state().get('selection').first().toJSON();
            console.log(attachment.id)
            $('#event_image_hidden').val(attachment.id);
            $('#event_image_preview').html('<img src="' + attachment.url + '" style="max-width:100%; height:auto;">');
            $('#remove_event_image').show();
        });
        frame.open();
    });

    $('#remove_event_image').on('click', function (e) {
        e.preventDefault();
        $('#event_image').val('');
        $('#event_image_preview').html('');
        $(this).hide();
    });

})