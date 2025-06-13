<?php
/*
Plugin Name: AI Generate SEO Content
Plugin URI: https://example.com/plugins/ai-generate-seo-content
Description: A plugin to generate SEO content using AI with input from users.
Version: 1.0.0
Author: Your Name
Author URI: https://example.com
License: GPL2
Text Domain: ai-generate-seo-content
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Hook per creare la tabella al momento dell'attivazione del plugin
register_activation_hook(__FILE__, 'aigsc_update_db_table');

function aigsc_update_db_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'aigsc_urls';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        url varchar(255) NOT NULL,
        seo_description text NULL,
        seo_title varchar(255) NULL,
        seo_meta_description varchar(255) NULL,
		check_result_response varchar(255) NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Hook per aggiungere il menu di amministrazione
add_action('admin_menu', 'aigsc_add_admin_menu');

function aigsc_add_admin_menu() {
    add_menu_page(
        'AI Generate SEO Content',
        'AI Generate SEO Content',
        'manage_options',
        'ai-generate-seo-content',
        'aigsc_settings_page',
        'dashicons-edit',
        100
    );
}

// Pagina delle impostazioni
function aigsc_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('AI Generate SEO Content', 'ai-generate-seo-content'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('aigsc_settings_group');
            do_settings_sections('ai-generate-seo-content');
            submit_button();
            ?>
        </form>

        <?php aigsc_add_textarea(); ?>
    </div>
    <?php
}

// Registrazione delle impostazioni
add_action('admin_init', 'aigsc_register_settings');

function aigsc_register_settings() {
    register_setting('aigsc_settings_group', 'aigsc_serial_key');
    register_setting('aigsc_settings_group', 'aigsc_business_info');

    add_settings_section(
        'aigsc_section',
        __('Configuration', 'ai-generate-seo-content'),
        null,
        'ai-generate-seo-content'
    );

    add_settings_field(
        'aigsc_serial_key',
        __('Serial Key', 'ai-generate-seo-content'),
        'aigsc_serial_key_callback',
        'ai-generate-seo-content',
        'aigsc_section'
    );

    add_settings_field(
        'aigsc_business_info',
        __('Business Info', 'ai-generate-seo-content'),
        'aigsc_business_info_callback',
        'ai-generate-seo-content',
        'aigsc_section'
    );
}

function aigsc_serial_key_callback() {
    $serial_key = get_option('aigsc_serial_key');
    echo '<input type="text" name="aigsc_serial_key" value="' . esc_attr($serial_key) . '" class="regular-text">';
}

function aigsc_business_info_callback() {
    $business_info = get_option('aigsc_business_info');
    echo '<input type="text" name="aigsc_business_info" value="' . esc_attr($business_info) . '" class="regular-text">';
}

// Funzione per visualizzare la text area e gestire il salvataggio dei dati
// Funzione per visualizzare la text area principale e la tabella per la gestione dei campi aggiuntivi
function aigsc_add_textarea() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'aigsc_urls';
    $rows = $wpdb->get_results("SELECT * FROM $table_name");


    // Gestione del salvataggio dei dati dalla text area principale
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aigsc_additional_data'])) {
        check_admin_referer('aigsc_save_data_nonce', 'aigsc_nonce');

        $data_lines = explode("\n", sanitize_textarea_field($_POST['aigsc_additional_data']));
        $wpdb->query("DELETE FROM $table_name");

        foreach ($data_lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                list($name, $url) = explode('|', $line);
                $wpdb->insert($table_name, [
                    'name' => sanitize_text_field(trim($name)),
                    'url' => esc_url_raw(trim($url))
                ]);
            }
        }
        echo '<div id="message" class="updated notice is-dismissible"><p>' . esc_html__('Data saved successfully.', 'ai-generate-seo-content') . '</p></div>';
    }

    // Gestione del salvataggio dei dati SEO aggiuntivi
    foreach ($rows as $row) {
        if (isset($_POST['aigsc_save_row_' . $row->id])) {
            $seo_description = stripslashes($_POST['seo_description_' . $row->id]);
            $seo_title = $_POST['seo_title_' . $row->id];
            $seo_meta_description = $_POST['seo_meta_description_' . $row->id];
			
			$stmt = $wpdb->prepare(
				"UPDATE $table_name SET seo_description = %s, seo_title = %s, seo_meta_description = %s WHERE id = %d",
				$seo_description, $seo_title, $seo_meta_description, $row->id
			);
			$wpdb->query($stmt);

            /*$wpdb->update(
                $table_name,
                [
                    'seo_description' => $seo_description,
                    'seo_title' => $seo_title,
                    'seo_meta_description' => $seo_meta_description
                ],
                ['id' => $row->id]
            );*/

            echo '<div id="message" class="updated notice is-dismissible"><p>' . esc_html__('Data saved successfully.', 'ai-generate-seo-content') . '</p></div>';
        }

        // Gestione dell'eliminazione delle righe
        if (isset($_POST['aigsc_delete_row_' . $row->id])) {
            $wpdb->delete($table_name, ['id' => $row->id]);
            echo '<div id="message" class="updated notice is-dismissible"><p>' . esc_html__('Row deleted successfully.', 'ai-generate-seo-content') . '</p></div>';
        }
    }

    // Gestione della generazione delle descrizioni SEO tramite AI
    if (isset($_POST['aigsc_generate_seo'])) {
        // Assicuriamoci che row_id sia sempre un array
        $selected_rows = isset($_POST['row_id']) ? (array) $_POST['row_id'] : [];
        $serial_key = get_option('aigsc_serial_key');
        $business_info = get_option('aigsc_business_info');

        if ($serial_key && $business_info && !empty($selected_rows)) {
            foreach ($selected_rows as $row_id) {
                $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $row_id));
                if ($row) {
                    $seo_description = agsc_call_openai_api($row, $serial_key, $business_info, $rows);
                    if ($seo_description) {
                        $wpdb->update(
                            $table_name,
                            ['seo_description' => $seo_description],
                            ['id' => $row_id]
                        );
						aigsc_call_openai_api_metatags($row, $business_info, $serial_key);
                    }		
                }
            }
            echo '<div id="message" class="updated notice is-dismissible"><p>' . esc_html__('SEO Descriptions generated successfully.', 'ai-generate-seo-content') . '</p></div>';
        } else {
            echo '<div id="message" class="error notice is-dismissible"><p>' . esc_html__('Please select rows and ensure Serial Key and Business Info are set.', 'ai-generate-seo-content') . '</p></div>';
        }
    }

	// Gestione della generazione dei meta tag SEO tramite AI
	if (isset($_POST['aigsc_generate_meta'])) {
		$selected_rows = isset($_POST['row_id']) ? (array) $_POST['row_id'] : [];
		$serial_key = get_option('aigsc_serial_key');
		$business_info = get_option('aigsc_business_info');

		if ($serial_key && $business_info && !empty($selected_rows)) {
			foreach ($selected_rows as $row_id) {
				$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $row_id));
				if ($row) {
					aigsc_call_openai_api_metatags($row, $business_info, $serial_key);
					//dopo la generazione di meta title e description procedo con il controllo
					// Prepara il contenuto per la verifica
					$cat_name = $row->name;
					$cat_url = $row->url;
					$cat_seo_description = $row->seo_description;
					$cat_meta_title = $row->seo_title;
					$cat_meta_description = $row->seo_meta_description;

					// Chiama l'API per il controllo del contenuto
					$check_result = call_check_content_api($row_id, $cat_name, $cat_url, $cat_seo_description, $cat_meta_title, $cat_meta_description, $serial_key);
				}
			}
			echo '<div id="message" class="updated notice is-dismissible"><p>' . esc_html__('Meta tags generated successfully.', 'ai-generate-seo-content') . '</p></div>';
		} else {
			echo '<div id="message" class="error notice is-dismissible"><p>' . esc_html__('Please select rows and ensure Serial Key and Business Info are set.', 'ai-generate-seo-content') . '</p></div>';
		}
	}
	
	// Gestione della verifica delle descrizioni SEO tramite AI
	if (isset($_POST['aigsc_check_result'])) {
		// Assicuriamoci che row_id sia sempre un array
		$selected_rows = isset($_POST['row_id']) ? (array) $_POST['row_id'] : [];
		$serial_key = get_option('aigsc_serial_key');

		if (!empty($selected_rows)) {
			foreach ($selected_rows as $row_id) {
				$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $row_id));
				if ($row) {
					// Prepara il contenuto per la verifica
					$cat_name = $row->name;
					$cat_url = $row->url;
					$cat_seo_description = $row->seo_description;
					$cat_meta_title = $row->seo_title;
					$cat_meta_description = $row->seo_meta_description;

					// Chiama l'API per il controllo del contenuto
					$check_result = call_check_content_api($row_id, $cat_name, $cat_url, $cat_seo_description, $cat_meta_title, $cat_meta_description, $serial_key);

					// Gestisci la risposta dell'API
					if ($check_result['status'] === 'success') {
						echo '<div id="message" class="updated notice is-dismissible"><p>' . esc_html__('Content checked successfully. Quality: ' . $check_result['quality'], 'ai-generate-seo-content') . '</p></div>';
					} else {
						echo '<div id="message" class="error notice is-dismissible"><p>' . esc_html__('Content check failed: ' . $check_result['message'], 'ai-generate-seo-content') . '</p></div>';
					}
				}
			}
		} else {
			echo '<div id="message" class="error notice is-dismissible"><p>' . esc_html__('Please select rows to check.', 'ai-generate-seo-content') . '</p></div>';
		}
	}




    // Recupero dei dati esistenti per la text area principale

    $textarea_content = '';
    foreach ($rows as $row) {
        $textarea_content .= $row->name . ' | ' . $row->url . "\n";
    }

    // Output della text area principale per inserire nuove righe
    ?>
    <div class="wrap">
        <h2><?php esc_html_e('Add New Data', 'ai-generate-seo-content'); ?></h2>
        <form method="post">
            <?php wp_nonce_field('aigsc_save_data_nonce', 'aigsc_nonce'); ?>
            <textarea name="aigsc_additional_data" rows="10" class="large-text"><?php echo esc_textarea($textarea_content); ?></textarea>
            <?php submit_button(__('Save Text Area Data', 'ai-generate-seo-content')); ?>
        </form>
    </div>
	<div class="wrap">
		<h2><?php esc_html_e('Upload CSV File', 'ai-generate-seo-content'); ?></h2>
		<form method="post" enctype="multipart/form-data">
			<?php wp_nonce_field('aigsc_upload_csv_nonce', 'aigsc_nonce'); ?>
			<input type="file" name="aigsc_csv_file" accept=".csv">
			<?php submit_button(__('Upload CSV Data', 'ai-generate-seo-content'), 'primary', 'submit_csv'); ?>
		</form>
	</div>

    <style>
        .aigsc-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }

        .aigsc-table th, .aigsc-table td {
            padding: 8px 12px;
            border: 1px solid #ddd;
            vertical-align: middle;
			text-align: center;
        }

        .aigsc-table th {
            background-color: #f4f4f4;
        }

        .aigsc-table th.check-column, 
        .aigsc-table td.check-column {
            width: 3%;
            text-align: center;
        }

        .aigsc-table th.column-name, 
        .aigsc-table td.column-name {
            width: 8%;
        }

        .aigsc-table th.column-url, 
        .aigsc-table td.column-url, 
		.aigsc-table th.column-actions,
		.aigsc-table td.column-actions,{
            width: 5%;
        }

        .aigsc-table th.column-seo-description, 
        .aigsc-table td.column-seo-description {
            width: 20%;
        }

        .aigsc-table th.column-title, 
        .aigsc-table td.column-title {
            width: 10%;
        }

        .aigsc-table th.column-meta-description, 
        .aigsc-table td.column-meta-description {
            width: 10%;
        }

        .aigsc-table .large-text {
            width: 100%;
            height: 100px;
        }

        .aigsc-table .regular-text {
            width: 100%;
        }

        .aigsc-table td textarea, 
        .aigsc-table td input {
            width: 100%;
            box-sizing: border-box;
        }

        .aigsc-table td .button {
            margin-top: 8px;
        }
    </style>
	<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.4/js/dataTables.buttons.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script> <!-- Necessario per le font necessarie a pdfMake -->
	<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
	<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
	<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
	<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">

<script type="text/javascript">
jQuery(document).ready(function($) {
    var table = $('#agsc-results-table').DataTable({
        "pageLength": 10,
        "lengthMenu": [[10, 25, 50,100, 200,400, 500,1000, -1], [10, 25, 50,100, 200,400, 500,1000, "All"]],
    });

function applyCustomSearch() {
    var selectedColumn = $('#columnSelect').val();
    var showEmpty = $('#chk_empty').is(':checked');
    var showNonEmpty = $('#chk_non_empty').is(':checked');

    // Clear all custom search criteria
    $.fn.dataTable.ext.search.pop();
    $.fn.dataTable.ext.search.pop();

    if (showEmpty) {
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                // Cerca sia nei campi input che textarea
                var cell = $(table.cell(dataIndex, selectedColumn).node());
                var inputVal = cell.find('input').val();
                var textareaVal = cell.find('textarea').val();
                var value = inputVal !== undefined ? inputVal : textareaVal;
                return value === '';  // Mostra le righe dove la cella è vuota
            }
        );
    }

    if (showNonEmpty) {
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                // Cerca sia nei campi input che textarea
                var cell = $(table.cell(dataIndex, selectedColumn).node());
                var inputVal = cell.find('input').val();
                var textareaVal = cell.find('textarea').val();
                var value = inputVal !== undefined ? inputVal : textareaVal;
                return value !== '';  // Mostra le righe dove la cella non è vuota
            }
        );
    }

    table.draw();
}


    // Attach listeners to dropdown and checkboxes
    $('#columnSelect, #chk_empty, #chk_non_empty').on('change', applyCustomSearch);
});
</script>

    <?php

    // Visualizzazione della tabella con i dati esistenti e le opzioni per modificarli
// Visualizzazione della tabella con i dati esistenti e le opzioni per modificarli
if ($rows) {
    echo '<h2>' . esc_html__('Manage Existing Data', 'ai-generate-seo-content') . '</h2>';
echo '<div class="filter-controls" style="margin-bottom:20px">
        <label for="columnSelect">Seleziona Colonna:</label>
        <select id="columnSelect">
            <option value="1">Name</option>
            <option value="2">URL</option>
            <option value="3">SEO Description</option>
            <option value="4">Title</option>
            <option value="5">Meta Description</option>
			<option value="6">Check Result</option>
        </select>

        <div class="checkbox-group">
            <input type="checkbox" id="chk_empty" name="chk_empty">
            <label for="chk_empty">Mostra solo righe vuote nella colonna selezionata</label>
        </div>

        <div class="checkbox-group">
            <input type="checkbox" id="chk_non_empty" name="chk_non_empty">
            <label for="chk_non_empty">Mostra solo righe non vuote nella colonna selezionata</label>
        </div>
      </div>';


    echo '<form method="post">';
    echo '<table class="aigsc-table" id="agsc-results-table">';
    echo '<thead><tr><th class="check-column"><input type="checkbox" /></th><th class="column-name">' . esc_html__('Name', 'ai-generate-seo-content') . '</th><th class="column-url">' . esc_html__('URL', 'ai-generate-seo-content') . '</th><th class="column-seo-description">' . esc_html__('SEO Description', 'ai-generate-seo-content') . '</th><th class="column-title">' . esc_html__('Title', 'ai-generate-seo-content') . '</th><th class="column-meta-description">' . esc_html__('Meta Description', 'ai-generate-seo-content') . '</th><th class="column-check-result">' . esc_html__('Check Result', 'ai-generate-seo-content') . '</th><th class="column-actions">' . esc_html__('Actions', 'ai-generate-seo-content') . '</th></tr></thead>';

    echo '<tbody>';

    foreach ($rows as $row) {
        $seo_description = isset($row->seo_description) ? $row->seo_description : '';
        $seo_title = isset($row->seo_title) ? $row->seo_title : '';
        $seo_meta_description = isset($row->seo_meta_description) ? $row->seo_meta_description : '';
        $check_result_response = isset($row->check_result_response) ? $row->check_result_response : '';

        echo '<tr>';
        echo '<th scope="row" class="check-column"><input type="checkbox" name="row_id[]" value="' . esc_attr($row->id) . '" /></th>';
        echo '<td class="column-name">' . esc_html($row->name) . '</td>';
        echo '<td class="column-url">' . esc_html($row->url) . '</td>';
        echo '<td class="column-seo-description"><textarea name="seo_description_' . $row->id . '" rows="4" col="30">' . esc_textarea($seo_description) . '</textarea></td>';
        echo '<td class="column-title"><input type="text" name="seo_title_' . $row->id . '" value="' . esc_attr($seo_title) . '" class="regular-text" /></td>';
        echo '<td class="column-meta-description"><input type="text" name="seo_meta_description_' . $row->id . '" value="' . esc_attr($seo_meta_description) . '" class="regular-text" /></td>';
        echo '<td class="column-check-result"><textarea name="column_check_result_' . $row->id . '" rows="4">' . esc_textarea($check_result_response) . '</textarea></td>';
        echo '<td class="column-actions">
                <button type="submit" name="aigsc_save_row_' . $row->id . '" class="button button-primary" value="save">' . esc_html__('Save Data', 'ai-generate-seo-content') . '</button>
                <button type="submit" name="aigsc_delete_row_' . $row->id . '" class="button button-secondary" value="delete" onclick="return confirm(\'' . esc_js(__('Are you sure you want to delete this row?', 'ai-generate-seo-content')) . '\');">' . esc_html__('Delete Row', 'ai-generate-seo-content') . '</button>
              </td>';
        echo '</tr>';
    }        

    echo '</tbody>';
    echo '</table>';
    // Pulsante per generare descrizioni SEO
    echo '<p><button type="submit" name="aigsc_generate_seo" class="button button-primary">' . esc_html__('Generate SEO Descriptions with AI', 'ai-generate-seo-content') . '</button></p>';
    // Pulsante per generare meta tag SEO
    echo '<p><button type="submit" name="aigsc_generate_meta" class="button button-primary">' . esc_html__('Generate SEO Meta Tags with AI', 'ai-generate-seo-content') . '</button></p>';
    // Pulsante per controllare i risultati
    echo '<p><button type="submit" name="aigsc_check_result" class="button button-primary">' . esc_html__('Check Result Content with AI', 'ai-generate-seo-content') . '</button></p>';

    echo '<p><button type="submit" name="aigsc_download_csv" class="button button-primary">' . esc_html__('Download Table as CSV', 'ai-generate-seo-content') . '</button></p>';

    echo '</form>';
    // Aggiungi il pulsante per copiare la tabella
    echo '<p><button id="copyButton">Copia Tabella</button></p>';
        // Script per generare il file Excel
        ?><script>
function copyTableToClipboard(tableID) {
    var table = document.getElementById(tableID);
    var rows = table.querySelectorAll('tr');
    var textToCopy = '';

    // Itera su tutte le righe della tabella
    rows.forEach(function(row) {
        var cols = row.querySelectorAll('th, td');

        cols.forEach(function(col, index) {
            var cellContent = '';

            // Salta la prima colonna, la colonna "Check Result" e "Actions"
            if (index === 0 || index === cols.length - 1) {
                return; // Usa 'return' per saltare l'iterazione corrente del loop forEach
            }

            // Se la colonna contiene un textarea
            if (col.querySelector('textarea')) {
                cellContent = col.querySelector('textarea').value;
            }
            // Se la colonna contiene un input text
            else if (col.querySelector('input[type="text"]')) {
                cellContent = col.querySelector('input[type="text"]').value;
            }
            // Altrimenti prendi il testo normale
            else {
                cellContent = col.innerText.trim() || col.textContent.trim();
            }

            textToCopy += cellContent;

            // Aggiungi una tabulazione tra le colonne, eccetto l'ultima
            if (index < cols.length - 1) {
                textToCopy += "\t";
            }
        });

        // Aggiungi una nuova riga tra le righe della tabella
        textToCopy += "\n";
    });

    // Crea un textarea temporaneo per copiare il testo negli appunti
    var tempTextArea = document.createElement('textarea');
    tempTextArea.style.position = 'absolute';
    tempTextArea.style.left = '-9999px';
    tempTextArea.value = textToCopy;

    document.body.appendChild(tempTextArea);
    tempTextArea.select();
    document.execCommand('copy');
    document.body.removeChild(tempTextArea);

    alert('Tabella copiata negli appunti!');
}

// Esempio di utilizzo
document.getElementById("copyButton").addEventListener("click", function () {
    copyTableToClipboard("agsc-results-table");
});


        </script>
    <?php } else {
        echo '<p>' . esc_html__('No data found.', 'ai-generate-seo-content') . '</p>';
    }
}

// Funzione per costruire il prompt per una singola riga
function aigsc_build_prompt($current_row, $rows) {
    $url_list = '';
    
    // Iteriamo attraverso l'array per ottenere i permalink
    foreach ($rows as $row) {
        // Non includere il nome e il link della riga corrente
        if ($current_row->name != $row->name) {
        
            $url_list .= '#Nome: ' . $row->name . ' | URL: ' . $row->url . " \n\n";
        }
    }
    $prompt = "[istruzioni]:\n\nMi devi creare una descrizione ottimizzata SEO per la pagina di categoria che ha come main keyword: {$current_row->name}.\n\n";
	$prompt .= "La descrizione deve contenere al massimo 500 caratteri e MOLTO IMPORTANTE della stessa lingua della main kw e della url.\nUtilizza grassetti strategici per i termini chiave. Evita il keyword stuffing.";
    // Costruisce il prompt specifico per la riga corrente
    //$prompt = "[istruzioni]: \n\nGenera un testo descrittivo di massimo 100 parole ottimizzato per la SEO per la categoria: {$current_row->name}.\n\n";
    //$prompt .= "Inserisci link interni con anchor text coerenti utilizzando unicamente le coppie di 'Nome | URL' di seguito:\n\n" . $url_list;
    
    return $prompt;
}


// Funzione per chiamare l'endpoint del plugin che gestisce la generazione del testo tramite API OpenAI e salva il risultato nel database
function agsc_call_openai_api($row, $serial_key, $info, $all_rows) {
    
    $url = 'https://api.clementeteodonno.it/wp-json/myplugin/v1/generate-category-description?user_key='.$serial_key; // Endpoint API personalizzato
    
    // Genera il prompt utilizzando la funzione aigsc_build_prompt
    $prompt = aigsc_build_prompt($row, $all_rows);
	$prompt .= "\n\n[info business]: $info\n\n";
    $postData = [
        'prompt_text_system' => 'Sei un esperto di SEO e copywriter. Segui le [istruzioni] e [info business] di seguito per generare una descrizione ottimizzata SEO. L\'output dovrebbe essere del codice HTML che inizia direttamente con il contenuto della descrizione, senza includere i tag di apertura <html> o <body>',
        'prompt_text' => $prompt,
        'chatgpt_model' => 'gpt-4-turbo' // Specifica il modello da utilizzare
    ];
    
    // Inizializza cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    
    // Esegui la chiamata API
    $response = curl_exec($ch);
	
    if (curl_errno($ch)) {
        echo '<p>' . esc_html__('Errore durante la generazione del testo SEO:', 'ai-generate-seo-content') . ' ' . curl_error($ch) . '</p>';
        return false;
    }
    $response_data = json_decode($response, true);
	//var_dump($response_data);die();
    $seo_text = $response_data['result']['choices'][0]['message']['content'] ?? false;
    if ($seo_text) {
        return $seo_text;
    }
	return false;
}

// Funzione per chiamare l'endpoint del plugin che gestisce la generazione dei meta tag SEO tramite API OpenAI e salva il risultato nel database
function aigsc_call_openai_api_metatags($row, $business_info, $serial_key) {
	
	$path = parse_url($row->url, PHP_URL_PATH); // Estrae il path dall'URL
	$segments = explode('/', trim($path, '/')); // Divide il path in segmenti, rimuovendo gli slash iniziali e finali

	$lang = $segments[0]; // Il primo segmento dopo il dominio è il pezzo della lingua
	//var_dump($lang);die();
    $url = 'https://api.clementeteodonno.it/wp-json/myplugin/v1/generate-seo-metatags?user_key=' . $serial_key; // Endpoint API personalizzato

    $postData = [
        'titolo' => $row->seo_title,
		'keyword' => $row->name,
        'contenuto' => $row->seo_description,
        'info' => $business_info,
		'lang' => $lang, 
    ];

    // Inizializza cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    
    // Esegui la chiamata API
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo '<p>' . esc_html__('Errore durante la generazione dei meta tag SEO:', 'ai-generate-seo-content') . ' ' . curl_error($ch) . '</p>';
        return false;
    }
    $title_description = json_decode($response, true);
    //var_dump($title_description);die();
    if (is_array($title_description) && isset($title_description['result'])) {
        if (isset($title_description['result']['title']) && isset($title_description['result']['description'])) {
            // Estrai title e description
            $title = sanitize_text_field($title_description['result']['title']);
            $description = sanitize_textarea_field($title_description['result']['description']);

            // Salva title e description nel database associato alla riga specifica
            global $wpdb;
            $table_name = $wpdb->prefix . 'aigsc_urls';
            $wpdb->update(
                $table_name,
                [
                    'seo_title' => $title,
                    'seo_meta_description' => $description
                ],
                ['id' => $row->id]
            );

        } else {
            error_log('Mancano title o description in result.');
            return; // Termina l'esecuzione della funzione se i campi non sono presenti
        }
    } else {
		echo '<p>' . esc_html__('Errore durante la generazione dei meta tag SEO:', 'ai-generate-seo-content') . ' ' .$response. '</p>';
        return; // Termina l'esecuzione della funzione se i campi non sono presenti
	}
}

function call_check_content_api($row_id, $cat_name, $cat_url, $cat_seo_description, $cat_meta_title, $cat_meta_description, $serial_key) {
    $url = 'https://api.clementeteodonno.it/wp-json/myplugin/v1/check-content-ai?user_key=' . $serial_key;
    $postData = [
        'category_name' => $cat_name,
        'category_url' => $cat_url,
        'seo_description' => $cat_seo_description,
        'meta_title' => $cat_meta_title,
        'meta_description' => $cat_meta_description
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Errore durante la chiamata API: ' . curl_error($ch);
        return ['status' => 'error', 'message' => curl_error($ch)];
    }
    curl_close($ch);
	
	$response_data = json_decode($response, true);

    // Verifica se la chiave 'feedback' è presente nel risultato
    if (isset($response_data['result']) && isset($response_data['result']['feedback'])) {
		
		global $wpdb;
        $table_name = $wpdb->prefix . 'aigsc_urls';
        $feedback = $response_data['result']['feedback'];
		
        // Aggiorna il database con il feedback
        $wpdb->update(
            $table_name,
            ['check_result_response' => $feedback],
            ['id' => $row_id]
        );
		
		/*if ($feedback !== 'Controllo superato.'){
			call_update_result_response($row_id, $cat_name, $cat_url, $cat_seo_description, $cat_meta_title, $cat_meta_description, $serial_key, $feedback);
		}*/
		
        return ['status' => 'success', 'message' => 'Content checked and database updated.', 'feedback' => $feedback];
    } else {
        return ['status' => 'error', 'message' => 'Failed to get a valid response from the API or missing feedback.'];
    }
}


function call_update_result_response($row_id, $cat_name, $cat_url, $cat_seo_description, $cat_meta_title, $cat_meta_description, $serial_key, $feedback) {
    $url = 'https://api.clementeteodonno.it/wp-json/myplugin/v1/update-result-response?user_key=' . $serial_key;
    $postData = [
        'category_name' => $cat_name,
        'category_url' => $cat_url,
        'seo_description' => $cat_seo_description,
        'meta_title' => $cat_meta_title,
        'meta_description' => $cat_meta_description,
		'feedback' => $feedback
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Errore durante la chiamata API: ' . curl_error($ch);
        return ['status' => 'error', 'message' => curl_error($ch)];
    }
    curl_close($ch);
	
	$response_data = json_decode($response, true);
	//var_dump($response_data);die();
    // Verifica se la chiave 'seo_description' è presente nel risultato
    if (isset($response_data['result'])) {
		
		global $wpdb;
        $table_name = $wpdb->prefix . 'aigsc_urls';
		
		if (isset($response_data['result']['seo_description'])) {
			$new_seo_description = $response_data['result']['seo_description'];		
			// Aggiorna il database con il feedback
			$wpdb->update(
				$table_name,
				['seo_description' => $new_seo_description],
				['id' => $row_id]
			);
    	}
		if (isset($response_data['result']['meta_title'])) {
			$new_meta_title = $response_data['result']['meta_title'];		
			// Aggiorna il database con il feedback
			$wpdb->update(
				$table_name,
				['seo_title' => $new_meta_title],
				['id' => $row_id]
			);
    	}
		if (isset($response_data['result']['meta_description'])) {
			$new_meta_description = $response_data['result']['meta_description'];		
			// Aggiorna il database con il feedback
			$wpdb->update(
				$table_name,
				['seo_meta_description' => $new_meta_description],
				['id' => $row_id]
			);
    	}
	}
}

// Funzione per scaricare la tabella come CSV
function aigsc_download_csv() {
    if (isset($_POST['aigsc_download_csv'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'aigsc_urls';
        
        // Recupera i dati dalla tabella
        $rows = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

        if (!empty($rows)) {
            // Imposta intestazioni per il download del CSV
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=seo_data.csv');

            // Crea un puntatore al file di output
            $output = fopen('php://output', 'w');

            // Inserisci l'intestazione del CSV
            fputcsv($output, array('ID', 'Name', 'URL', 'SEO Description', 'Title', 'Meta Description'));

            // Inserisci i dati nel CSV
            foreach ($rows as $row) {
                fputcsv($output, $row);
            }

            // Chiudi il file di output
            fclose($output);
            exit;
        } else {
            echo '<div id="message" class="error notice is-dismissible"><p>' . esc_html__('No data available for download.', 'ai-generate-seo-content') . '</p></div>';
        }
    }
}
add_action('admin_init', 'aigsc_download_csv');

function aigsc_upload_csv_data() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_csv'])) {
        check_admin_referer('aigsc_upload_csv_nonce', 'aigsc_nonce');

        if (isset($_FILES['aigsc_csv_file']['tmp_name'])) {
            $file_path = $_FILES['aigsc_csv_file']['tmp_name'];
            if (!empty($file_path)) {
                global $wpdb;
                $table_name = $wpdb->prefix . 'aigsc_urls';
                $file = fopen($file_path, 'r');

                // Opzionale: Salta l'intestazione se il tuo CSV ha una riga di intestazione
                fgetcsv($file);

                while (($column = fgetcsv($file, 3000, ",")) !== FALSE) {
                    $wpdb->insert($table_name, [
                        'name' => sanitize_text_field(trim($column[0])),
                        'url' => esc_url_raw(trim($column[1])),
                        'seo_description' => isset($column[2]) ? $column[2] : '',
                        'seo_title' => isset($column[3]) ? $column[3]: '',
                        'seo_meta_description' => isset($column[4]) ? $column[4] : ''
                    ]);
                }
                fclose($file);
                echo '<div id="message" class="updated notice is-dismissible"><p>' . esc_html__('CSV Data imported successfully.', 'ai-generate-seo-content') . '</p></div>';
            }
        }
    }
}

add_action('admin_init', 'aigsc_upload_csv_data');

