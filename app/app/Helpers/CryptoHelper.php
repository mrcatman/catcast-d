<?php

namespace App\Helpers;

class CryptoHelper
{

    public static function generateKeys($object) {
        $pkiConfig = [
            'digest_alg' => 'sha512',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];
        $pki = openssl_pkey_new($pkiConfig);
        openssl_pkey_export($pki, $pki_private);
        $pki_public = openssl_pkey_get_details($pki);
        $pki_public = $pki_public['key'];

        $object->private_key = $pki_private;
        $object->public_key = $pki_public;
    }
}
