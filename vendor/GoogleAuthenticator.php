<?php
// vendor/GoogleAuthenticator.php
class GoogleAuthenticator {
    public static function generateSecret($length = 16) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i=0;$i<$length;$i++) $secret .= $chars[random_int(0, strlen($chars)-1)];
        return $secret;
    }
    public static function getQRCodeGoogleUrl($name, $secret, $title = null) {
        $urlencoded = urlencode("otpauth://totp/{$name}?secret={$secret}" . ($title ? "&issuer=" . urlencode($title) : ''));
        return "https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl={$urlencoded}";
    }
    public static function getCode($secret, $timeSlice = null) {
        if ($timeSlice === null) $timeSlice = floor(time() / 30);
        $secretkey = self::base32_decode($secret);
        $time = chr(0).chr(0).chr(0).chr(0).pack('N*', $timeSlice);
        $hash = hash_hmac('sha1', $time, $secretkey, true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $truncatedHash = substr($hash, $offset, 4);
        $value = unpack("N", $truncatedHash)[1] & 0x7FFFFFFF;
        $modulo = 1000000;
        return str_pad($value % $modulo, 6, '0', STR_PAD_LEFT);
    }
    public static function verifyCode($secret, $code, $discrepancy = 1, $currentTimeSlice = null) {
        if ($currentTimeSlice === null) $currentTimeSlice = floor(time() / 30);
        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculated = self::getCode($secret, $currentTimeSlice + $i);
            if ($calculated === $code) return true;
        }
        return false;
    }
    private static function base32_decode($secret) {
        if (empty($secret)) return '';
        $base32chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $base32charsFlipped = array_flip(str_split($base32chars));
        $paddingCharCount = substr_count($secret, '=');
        $allowedValues = [6,4,3,1,0];
        if (!in_array($paddingCharCount, $allowedValues)) return false;
        $secret = str_replace('=', '', $secret);
        $secret = strtoupper($secret);
        $binaryString = '';
        for ($i = 0; $i < strlen($secret); $i++) {
            $c = $secret[$i];
            if (!isset($base32charsFlipped[$c])) return false;
            $binaryString .= str_pad(decbin($base32charsFlipped[$c]), 5, '0', STR_PAD_LEFT);
        }
        $eightBits = str_split($binaryString, 8);
        $decoded = '';
        foreach ($eightBits as $bits) {
            if (strlen($bits) === 8) {
                $decoded .= chr(bindec($bits));
            }
        }
        return $decoded;
    }
}
