<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['base_url']	= '';


$config['index_page'] = 'index.php';


$config['uri_protocol']	= 'AUTO';

$config['url_suffix'] = '';


$config['language']	= 'english';


$config['charset'] = 'UTF-8';


$config['enable_hooks'] = FALSE;



$config['subclass_prefix'] = 'MY_';

$config['permitted_uri_chars'] = 'a-z 0-9~%.:_\-';


$config['allow_get_array']	= TRUE;
$config['enable_query_strings'] = FALSE;
$config['controller_trigger']	= 'c';
$config['function_trigger']	= 'm';
$config['directory_trigger']	= 'd'; 


$config['log_threshold'] = 1;


$config['log_path'] = '';


$config['log_date_format'] = 'Y-m-d H:i:s';


$config['cache_path'] = '';


$config['encryption_key'] = 'YJ9FljXV4axG7QTzEzbRaUBFwi0FzIls';


$config['sess_cookie_name']	= 'jorani_session';
$config['sess_expiration']	= 7200;
$config['sess_expire_on_close']	= FALSE;
$config['sess_encrypt_cookie']	= TRUE;
$config['sess_use_database']	= FALSE;
$config['sess_table_name']	= 'ci_sessions';
$config['sess_match_ip']	= FALSE;
$config['sess_match_useragent']	= TRUE;
$config['sess_time_to_update']	= 300;


$config['cookie_prefix']	= "";
$config['cookie_domain']	= "";
$config['cookie_path']		= "/";
$config['cookie_secure']	= FALSE;


$config['global_xss_filtering'] = FALSE;


if (isset($_SERVER["REQUEST_URI"])) 
{
    if(stripos($_SERVER["REQUEST_URI"],'/api/') === FALSE)
    {
        $config['csrf_protection'] = TRUE;
    }
    else
    {
        $config['csrf_protection'] = FALSE;
    } 
} 
else 
{
    $config['csrf_protection'] = TRUE;
}
$config['csrf_token_name'] = 'csrf_test_jorani';
$config['csrf_cookie_name'] = 'csrf_cookie_jorani';
$config['csrf_expire'] = 7200;


$config['compress_output'] = FALSE;


$config['time_reference'] = 'local';


$config['rewrite_short_tags'] = FALSE;


$config['proxy_ips'] = '';


$config['from_mail'] = 'do.not@reply.me';
$config['from_name'] = 'Jorani';
$config['subject_prefix'] = '[Jorani] ';


$config['password_length'] = 8;

$config['default_role_id'] = 2;


$config['leave_status_requested'] = FALSE;
$config['default_leave_type'] = FALSE;      


$config['disable_edit_leave_duration'] = FALSE;             


$config['delete_rejected_requests'] = FALSE;
$config['edit_rejected_requests'] = FALSE;


$config['requests_by_manager'] = FALSE;


$config['languages'] = 'en,fr,es,nl,de,it,ru';


$config['disable_overtime'] = FALSE;


$config['ga_code'] = '';
$config['ga_send_userid'] = FALSE;


$config['ldap_enabled'] = FALSE;
$config['ldap_host'] = '127.0.0.1';
$config['ldap_port'] = 389;
$config['ldap_basedn'] = 'uid=%s,ou=people,dc=company,dc=com'; 
$config['ldap_basedn_db'] = FALSE;   


$config['oauth2_enabled'] = FALSE;
$config['oauth2_provider'] = 'google'; 
$config['oauth2_client_id'] = '';
$config['oauth2_client_secret'] = '';


$config['ics_enabled'] = TRUE;
$config['default_timezone'] = 'Europe/Paris';


$config['public_calendar'] = FALSE;
