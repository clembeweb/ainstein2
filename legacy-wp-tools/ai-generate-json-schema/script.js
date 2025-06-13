jQuery(document).ready(function($){
    var file_frame;

    $('#upload_sitemap_button').on('click', function(event){
        event.preventDefault();

        // Crea il media frame
        if (file_frame) {
            file_frame.open();
            return;
        }

        file_frame = wp.media.frames.file_frame = wp.media({
            title: 'Seleziona o carica la tua sitemap XML',
            button: {
                text: 'Usa questo file'
            },
            multiple: false
        });

        // Quando un file è selezionato
        file_frame.on('select', function() {
            var attachment = file_frame.state().get('selection').first().toJSON();
            $('#sitemap_url').val(attachment.url);
            $('#sitemap_filename').text(attachment.filename);
        });

        // Apre il media frame
        file_frame.open();
    });
});
