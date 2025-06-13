<?php
/*
Plugin Name: AI Generate SEO Schema
Description: Permette di inserire una lista di URL, salvarle nel DB e generare uno schema SEO.
Version: 1.0
Author: Il Tuo Nome
Text Domain: ai-generate-seo-schema
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Evita l'accesso diretto
}

add_filter('upload_mimes', function ($mimes) {
    $mimes['xml'] = 'text/xml';
    return $mimes;
});

add_filter('wp_check_filetype_and_ext', function($data, $file, $filename, $mimes) {
    if (!$data['ext'] && !$data['type'] && in_array(pathinfo($filename, PATHINFO_EXTENSION), ['xml'])) {
        $data['ext'] = 'xml';
        $data['type'] = 'text/xml';
    }
    return $data;
}, 100, 4);


// Aggiungi la pagina di amministrazione
add_action('admin_menu', 'ai_generate_seo_schema_menu');

function ai_generate_seo_schema_menu() {
    add_menu_page(
        'AI Generate SEO Schema',
        'AI SEO JSON Schema',
        'manage_options',
        'ai-generate-seo-schema',
        'ai_generate_seo_schema_page',
        'dashicons-admin-generic',
        100
    );
}

add_action('admin_enqueue_scripts', 'ai_enqueue_media_uploader');

function ai_enqueue_media_uploader() {
    wp_enqueue_media();
    wp_enqueue_script('ai-generate-seo-schema-script', plugins_url('script.js', __FILE__), array('jquery'), null, true);
}


// Crea la pagina di amministrazione

function ai_generate_seo_schema_page() {
    ?>
    <div class="wrap">
        <h1>AI Generate SEO Schema</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('ai_generate_seo_schema_options_group');
            do_settings_sections('ai-generate-seo-schema');
            submit_button('Salva Impostazioni');
            ?>
        </form>
        <button id="upload_sitemap_button" class="button">Upload Sitemap</button>
        <span id="sitemap_filename"></span>
        <form method="post" action="">            
            <?php
            submit_button('Genera Schema', 'primary', 'generate_schema');
            ?>
        </form>
        <?php
        // Carica e stampa i risultati salvati nel database
        $saved_results = get_option('ai_generate_seo_schema_results', []);
        ai_print_schema_results_table($saved_results);

        if (isset($_POST['generate_schema'])) {
            ai_process_urls_and_generate_schema();
        }

        if (isset($_POST['sitemap_url']) && !empty($_POST['sitemap_url'])) {
        
            ai_upload_sitemap_and_extract_urls($_POST['sitemap_url']);
        }
        ?>
    </div>
    <?php
}


// Registra le impostazioni
add_action('admin_init', 'ai_handle_table_actions');

add_action('admin_init', 'ai_generate_seo_schema_settings');

function ai_generate_seo_schema_settings() {
    register_setting('ai_generate_seo_schema_options_group', 'ai_generate_seo_schema_urls');
    register_setting('ai_generate_seo_schema_options_group', 'ai_generate_seo_schema_serial_key');
    register_setting('ai_generate_seo_schema_options_group', 'ai_generate_seo_schema_business_info');
    register_setting('ai_generate_seo_schema_options_group', 'ai_generate_seo_schema_sitemap_url', 'ai_process_sitemap_and_extract_urls');

    add_settings_section(
        'ai_generate_seo_schema_section',
        'Impostazioni Generali',
        'ai_generate_seo_schema_section_callback',
        'ai-generate-seo-schema'
    );

    add_settings_field(
        'ai_generate_seo_schema_urls',
        'URL da salvare',
        'ai_generate_seo_schema_urls_callback',
        'ai-generate-seo-schema',
        'ai_generate_seo_schema_section'
    );

    add_settings_field(
        'ai_generate_seo_schema_serial_key',
        'Serial Key',
        'ai_generate_seo_schema_serial_key_callback',
        'ai-generate-seo-schema',
        'ai_generate_seo_schema_section'
    );

    add_settings_field(
        'ai_generate_seo_schema_business_info',
        'Informazioni Aziendali',
        'ai_generate_seo_schema_business_info_callback',
        'ai-generate-seo-schema',
        'ai_generate_seo_schema_section'
    );

    // Campo nascosto per salvare l'URL della sitemap
    add_settings_field(
        'ai_generate_seo_schema_sitemap_url',
        '',
        'ai_generate_seo_schema_sitemap_url_callback',
        'ai-generate-seo-schema',
        'ai_generate_seo_schema_section'
    );
}

function ai_generate_seo_schema_section_callback() {
    echo 'Inserisci una lista di URL, la chiave seriale e le informazioni aziendali.';
}

function ai_generate_seo_schema_urls_callback() {
    $urls = get_option('ai_generate_seo_schema_urls', '');
    echo "<textarea name='ai_generate_seo_schema_urls' rows='10' cols='50' class='large-text'>$urls</textarea>";
}

function ai_generate_seo_schema_serial_key_callback() {
    $serial_key = get_option('ai_generate_seo_schema_serial_key', '');
    echo "<input type='text' name='ai_generate_seo_schema_serial_key' value='" . esc_attr($serial_key) . "' class='regular-text' />";
}

function ai_generate_seo_schema_business_info_callback() {
    $business_info = get_option('ai_generate_seo_schema_business_info', '');
    echo "<textarea name='ai_generate_seo_schema_business_info' rows='5' cols='50' class='large-text'>$business_info</textarea>";
}

function ai_generate_seo_schema_sitemap_url_callback() {
    $sitemap_url = get_option('ai_generate_seo_schema_sitemap_url', '');
    echo "<input type='hidden' id='sitemap_url' name='ai_generate_seo_schema_sitemap_url' value='" . esc_attr($sitemap_url) . "' />";
}

function ai_process_sitemap_and_extract_urls($sitemap_url) {
    if (!empty($sitemap_url)) {
        ai_upload_sitemap_and_extract_urls($sitemap_url);
    }
}


function ai_upload_sitemap_and_extract_urls($sitemap_url) {
    if (!empty($sitemap_url)) {
        // Estrai le URL dalla sitemap
        $urls = extract_urls_from_sitemap($sitemap_url);

        if (!is_wp_error($urls)) {
            // Salva le URL estratte nel database
            $existing_urls = get_option('ai_generate_seo_schema_urls', '');
            $new_urls = implode("\n", $urls);

            // Unisci le nuove URL con quelle esistenti
            if (empty($existing_urls)) {
                $final_urls = $new_urls;
            } else {
                $final_urls = $existing_urls . "\n" . $new_urls;
            }

            // Salva le URL nel database
            update_option('ai_generate_seo_schema_urls', $final_urls);
        } else {
            echo '<div class="error"><p>' . esc_html($urls->get_error_message()) . '</p></div>';
        }
    }
}




//////////////////////////////////////////////////////////////
function ai_process_urls_and_generate_schema() {
    $serial_key = get_option('ai_generate_seo_schema_serial_key', '');
    $business_info = get_option('ai_generate_seo_schema_business_info', '');
    $urls = explode("\n", get_option('ai_generate_seo_schema_urls', ''));
    $urls = array_map('trim', $urls);
    $saved_results = get_option('ai_generate_seo_schema_results', []);
    $scraped_results = [];

    foreach ($urls as $url) {
        if (empty($url)) {
            continue;
        }

        $scraped_content = agjs_scrape_url_api($url, $serial_key);
        
        if ($scraped_content) {
            // Genera lo schema JSON-LD utilizzando il contenuto estratto
            $schema = agjs_call_openai_api($url, $scraped_content, $business_info, $serial_key);

            // Verifica se la URL esiste già nei risultati salvati
            $updated = false;
            foreach ($saved_results as &$saved_result) {
                if ($saved_result['url'] === $url) {
                    // Se esiste, aggiorna lo schema JSON-LD
                    $saved_result['schema'] = $schema;
                    $updated = true;
                    break;
                }
            }

            if (!$updated) {
                // Se la URL non esiste nei risultati salvati, aggiungi un nuovo record
                $scraped_results[] = [
                    'url' => $url,
                    'schema' => $schema,
                ];
            }
        }
    }

    // Unisci i nuovi risultati (se presenti) con quelli già salvati
    $merged_results = array_merge($saved_results, $scraped_results);
    
    // Salva i risultati aggiornati nel database
    update_option('ai_generate_seo_schema_results', $merged_results);

    // Stampa i risultati in una tabella
    ai_print_schema_results_table($merged_results);
}


function ai_print_schema_results_table($results) {
    if (!empty($results)) {
        echo '<h2>Risultati della Generazione dello Schema</h2>';
        echo '<form method="post" action="">'; // Form per gestire aggiornamenti ed eliminazioni
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>URL</th><th>Schema JSON-LD</th><th>Azioni</th></tr></thead>';
        echo '<tbody>';
        foreach ($results as $index => $result) {
            echo '<tr>';
            echo '<td>' . esc_url($result['url']) . '</td>';
            echo '<td><textarea name="schema_json[' . $index . ']" rows="10" cols="50">' . esc_textarea($result['schema']) . '</textarea></td>';
            echo '<td>';
            echo '<input type="hidden" name="urls[' . $index . ']" value="' . esc_url($result['url']) . '" />';
            echo '<button type="submit" name="update_row" value="' . $index . '" class="button button-primary">Aggiorna riga</button>';
            echo ' ';
            echo '<button type="submit" name="delete_row" value="' . $index . '" class="button button-secondary">Elimina riga</button>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</form>';
    }
}

//////////////////////////////////////////////////////////////////////////
function ai_handle_table_actions() {
    if (isset($_POST['update_row'])) {
        $index = intval($_POST['update_row']);
        $urls = $_POST['urls'];
        $schema_json = wp_unslash($_POST['schema_json']);
        
        $results = get_option('ai_generate_seo_schema_results', []);
        
        if (isset($results[$index])) {
            // Aggiorna il JSON con il contenuto della textarea
            $results[$index]['schema'] = $schema_json[$index];
            update_option('ai_generate_seo_schema_results', $results);
            echo '<div class="updated"><p>Riga aggiornata con successo.</p></div>';
        }
    }
    
    if (isset($_POST['delete_row'])) {
        $index = intval($_POST['delete_row']);
        
        $results = get_option('ai_generate_seo_schema_results', []);
        
        if (isset($results[$index])) {
            // Elimina la riga dall'array
            unset($results[$index]);
            // Reindicizza l'array per mantenere gli indici consecutivi
            $results = array_values($results);
            update_option('ai_generate_seo_schema_results', $results);
            echo '<div class="updated"><p>Riga eliminata con successo.</p></div>';
        }
    }
}



function agjs_call_openai_api($url, $scraped_content, $business_info, $serial_key) {
    $api_url = 'https://api.clementeteodonno.it/wp-json/myplugin/v1/generate-category-description?user_key=' . $serial_key;
    
    $prompt = agjs_build_prompt($url, $scraped_content, $business_info);
	
    $postData = [
        'prompt_text_system' => $business_info,
        'prompt_text' => $prompt,
        'chatgpt_model' => 'gpt-4-turbo'
        
    ];
    
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    
    $response = curl_exec($ch);
	
    if (curl_errno($ch)) {
        echo '<p>' . esc_html__('Errore durante la generazione dello schema:', 'ai-generate-seo-schema') . ' ' . curl_error($ch) . '</p>';
        return false;
    }
    
    $response_data = json_decode($response, true);
    $schema = $response_data['result']['choices'][0]['message']['content'] ?? 'Errore nella generazione dello schema';
                // Rimuovi la stringa indesiderata se presente
    
    $schema = str_replace("```", "", $schema);
    return $schema;
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function agjs_build_prompt($url, $scraped_content, $business_info) {

    $prompt = "**URL pagina**: {$url}";
    $prompt .= "\n\n**Contenuto Pagina:**\n" . $scraped_content;
    return $prompt;
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function agjs_scrape_url_api($url, $serial_key) {
    $api_url = 'https://api.clementeteodonno.it/wp-json/myplugin/v1/scrape-url?user_key=' . $serial_key;
    $postData = ['url' => $url];
    
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        echo '<p>' . esc_html__('Errore durante il recupero dei contenuti dell\'URL:', 'ai-generate-seo-schema') . ' ' . curl_error($ch) . '</p>';
        return false;
    }
    
    $response_data = json_decode($response, true);
    
    if (isset($response_data['code']) && $response_data['code'] === 'rest_no_route') {
        echo '<p>' . esc_html__('Errore: Endpoint non trovato. Verifica che l\'URL e l\'API siano corretti.', 'ai-generate-seo-schema') . '</p>';
        return false;
    }

    return $response_data;
}


function extract_urls_from_sitemap($sitemap_url) {
    if (empty($sitemap_url)) {
        return new WP_Error('no_sitemap_url', 'Nessun URL della sitemap fornito', array('status' => 400));
    }

    $response = wp_remote_get($sitemap_url);

    if (is_wp_error($response)) {
        return new WP_Error('http_request_failed', 'Errore durante il recupero della sitemap: ' . $response->get_error_message(), array('status' => 500));
    }

    if (wp_remote_retrieve_response_code($response) !== 200) {
        return new WP_Error('invalid_response', 'Sitemap non trovata o accesso negato', array('status' => 404));
    }

    $sitemap_xml = wp_remote_retrieve_body($response);
    $xml = simplexml_load_string($sitemap_xml);

    if ($xml === false) {
        return new WP_Error('xml_parse_error', 'Errore durante l\'analisi della sitemap XML', array('status' => 500));
    }

    $urls = array();

    foreach ($xml->url as $url) {
        $urls[] = (string) $url->loc;
    }

    return $urls;
}
