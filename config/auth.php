<?php
return [
    'api' => [
        'jwt_key'              => auth_config('api.jwt_key', 'f2244f5316b70ef2887514b65caf795f'),
        'jwt_exp'              => auth_config('api.jwt_exp', 3600),
        'jwt_aud'              => auth_config('api.jwt_aud', 'a'),
        'jwt_iss'              => auth_config('api.jwt_iss', 's'),
        'enable_refresh_token' => (bool)auth_config('api.enable_refresh_token', true),
        'refresh_token_exp'    => (int)auth_config('api.refresh_token_exp', 1296000),
        'reuse_check'          => (bool)auth_config('api.reuse_check', true),
        'token_position'       => auth_config('api.token_position', 'header'),
        'token_field'          => auth_config('api.token_field', 'token'),
    ],
];