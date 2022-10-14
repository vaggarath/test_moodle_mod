<?php
$capabilities = array (
                
        'mod/vagodel:addcomment' => array (
            'riskbitmask' => RISK_SPAM,
            'captype' => 'write',
            'contextlevel' => CONTEXT_MODULE,
            'archetypes' => array (
                            'student' => CAP_ALLOW,
                            'teacher' => CAP_ALLOW,
                            'editingteacher' => CAP_ALLOW,
                            'manager' => CAP_ALLOW 
            ) 
        ),
        
        'mod/vagodel:edit' => array (
            'riskbitmask' => RISK_SPAM | RISK_XSS,
            'captype' => 'write',
            'contextlevel' => CONTEXT_MODULE,
            'archetypes' => array (
                            'editingteacher' => CAP_ALLOW,
                            'manager' => CAP_ALLOW 
            ) 
        ),
                
        'mod/vagodel:view' => array (
            'captype' => 'read',
            'contextlevel' => CONTEXT_MODULE,
            'archetypes' => array (
                            // 'guest' => CAP_ALLOW, //on n'autorise pas les guests
                            'student' => CAP_ALLOW,
                            'teacher' => CAP_ALLOW,
                            'editingteacher' => CAP_ALLOW,
                            'manager' => CAP_ALLOW 
            ) 
        ),
                
        'mod/vagodel:addinstance' => array (
            'riskbitmask' => RISK_XSS,
            'captype' => 'write',
            'contextlevel' => CONTEXT_COURSE,
            'archetypes' => array (
                            'editingteacher' => CAP_ALLOW,
                            'manager' => CAP_ALLOW 
            ),
            'clonepermissionsfrom' => 'moodle/course:manageactivities' 
        ) 
)
;

?>
