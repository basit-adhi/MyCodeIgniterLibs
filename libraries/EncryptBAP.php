<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * EncryptBAP.php
 * <br />Encrypt Text Class (EncryptBAP) and Encrypt Information Class (KeyBAP)
 * <br />
 * <br />This is an encrypt and decrypt helper
 * 
 * @author Basit Adhi Prabowo, S.T. <basit@unisayogya.ac.id>
 * @access public
 * @link https://github.com/basit-adhi/MyCodeIgniterLibs/blob/master/libraries/EncryptBAP.php
 */
class EncryptBAP
{
    /**
     *
     * @var CI super-object
     */
    protected $CI;
    /**
     *
     * @var keyBAP store encryption information, needed for decrypt
     */
    private $key;
    /**
     *
     * @var string name of the encryption in session
     */
    private $name;
    
    // We'll use a constructor, as you can't directly call a function
    // from a property definition.
    function __construct()
    {
        // Assign the CodeIgniter super-object
        $this->CI =& get_instance();
        //--
        $this->key = new KeyBAP();
    }

    /**
     * Encrypt given plain text, all information saved to session with given name
     * @param string $name      name of the encryption in session
     * @param string $plaintext text to encrypt
     * @return string encrypted text
     */
    function encrypt($name, $plaintext)
    {
        $ciphertext = "";
        //$key should have been previously generated in a cryptographically safe way, like openssl_random_pseudo_bytes
        $this->key->cipher = (version_compare(PHP_VERSION, '7.1.0', '>=')) ? "aes-128-gcm" : "aes-128-cbc";
        if (in_array($this->key->cipher, openssl_get_cipher_methods()))
        {
            $this->key->randomiv(openssl_cipher_iv_length($this->key->cipher));
            if (version_compare(PHP_VERSION, '5.3.3', '<'))
            {
                $ciphertext         = openssl_encrypt($plaintext, $this->key->cipher, $this->key->key, $this->key->options=OPENSSL_RAW_DATA, $this->key->iv, $this->key->tag);
            }
            else
            {
                $ciphertext         = openssl_encrypt($plaintext, $this->key->cipher, $this->key->key, $this->key->options=OPENSSL_RAW_DATA, $this->key->iv);
            }
            $this->name         = $name;
            //store $cipher, $iv, and $tag for decryption later
            $this->savekey();
        }
        return base64_encode($ciphertext);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Decrypt cipher text, all information loaded from session with given name
     * @param string $name          name of the encryption in session
     * @param string $ciphertext    text to decrypt
     * @return string original text
     */
    function decrypt($name, $ciphertext)
    {
        $originaltext       = base64_decode($ciphertext);
        $this->name         = $name;
        $this->loadkey();
        if (in_array($this->key->cipher, openssl_get_cipher_methods()))
        {
            if (version_compare(PHP_VERSION, '5.3.3', '<'))
            {
                return openssl_decrypt($originaltext, $this->key->cipher, $this->key->key, $this->key->options=OPENSSL_RAW_DATA, $this->key->iv, $this->key->tag);
            }
            else
            {
                return openssl_decrypt($originaltext, $this->key->cipher, $this->key->key, $this->key->options=OPENSSL_RAW_DATA, $this->key->iv);
            }
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Save all information to session
     */
    private function savekey()
    {
        $this->key->serialize();
        $this->CI->session->set_userdata("encryptBAPkey".$this->name, $this->key->serialize());
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Load all information from session
     */
    private function loadkey()
    {
        $this->key = new KeyBAP();
        $this->key->unserialize($this->CI->session->userdata("encryptBAPkey".$this->name));
    }
}

class KeyBAP
{
    //all variable
    public $cipher;
    public $key;
    public $options;
    public $iv;
    public $tag;
    
    // We'll use a constructor, as you can't directly call a function
    // from a property definition.
    function __construct()
    {
        $this->cipher   = "";
        $this->randomkey(10);
        $this->options  = "";
        $this->iv       = "";
        $this->tag      = "";
    }
    
    // --------------------------------------------------------------------
    
    /**
     * String built from random chars
     * @param int $length   random string length
     * @return string random string
     */
    private function randchar($length)
    {
        $str    = "";
	$chars  = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@!#$%*";	
	$size   = strlen($chars);
	for ($i = 0; $i < $length; $i++)
        {
		$str .= $chars[ rand( 0, $size - 1 ) ];
	}
	return $str;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Key built from random chars
     * @param int $length   random key length
     */
    function randomkey($length)
    {
        $this->key  = $this->randchar($length);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Iv built from random chars
     * @param int $length   random iv length
     */
    function randomiv($length)
    {
        $this->iv   = $this->randchar($length);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Format information before stored to session
     * @return string   base64 serialize information
     */
    function serialize()
    {
        return base64_encode(serialize(array("cipher"=>$this->cipher, "key"=>$this->key, "options"=>$this->options, "iv"=>$this->iv, "tag"=>$this->tag)));
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Load information from session
     * @param string $sessionvalue  information from session
     */
    function unserialize($sessionvalue)
    {
        $s  = (array) unserialize(base64_decode($sessionvalue));
        $this->cipher   = $s["cipher"];
        $this->key      = $s["key"];
        $this->options  = $s["options"];
        $this->iv       = $s["iv"];
        $this->tag      = $s["tag"];
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Print all information to screen
     */
    function print_()
    {
        echo "cipher:".$this->cipher."|key:".$this->key."|options:".$this->options."|iv:".$this->iv."|tag:".$this->tag."z";
    }
}
/**
EXAMPLE
//if you load from application/library
//$this->CI =& get_instance();
//$this->CI->load->library('EncryptBAP');
//$enc = $this->CI->encryptbap->encrypt($name, "sometext");
//$this->CI->encryptbap->decrypt($name, $enc);
$this->load->library('EncryptBAP');
$enc = $this->encryptbap->encrypt($name, "sometext");
$enc = $this->encryptbap->decrypt($name, $enc);
 */
