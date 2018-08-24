<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Encryptbap.php
 * <br />Encrypt Text Class (EncryptBAP)
 * <br />
 * <br />This is an encrypt and decrypt helper
 * <br/>IMPORTANT:
 * <br/>if use moresecure mode, 1 name for 1 encrypt-decrypt, because every encrypt will generate unique tag
 * @author Basit Adhi Prabowo, S.T. <basit@unisayogya.ac.id>
 * @access public
 * @link https://github.com/basit-adhi/MyCodeIgniterLibs/blob/master/libraries/Encryptbap.php
 */
class Encryptbap
{
    /**
     *
     * @var CI super-object
     */
    protected $CI;
    /**
     *
     * @var array store encryption information, needed for decrypt
     */
    private $key;
    /**
     *
     * @var string name of the encryption in session
     */
    private $name;
    /**
     *
     * @var boolean is in mode debug? 
     */
    private $debug = false;
    /**
     *
     * @var boolean is more secure mode? (generate new tag every encrypt)
     */
    private $moresecure = false;
    
    // We'll use a constructor, as you can't directly call a function
    // from a property definition.
    function __construct()
    {
        // Assign the CodeIgniter super-object
        $this->CI =& get_instance();
        //--
        $this->key["type"]  = "";
        $this->key["tag"]   = "";
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Generate key if there is no key exists yet (then all information save to session with given name)
     * @param string $name          name of the encryption in session. IMPORTANT: if use moresecure mode, 1 name for 1 encrypt-decrypt, because every encrypt will generate unique tag
     * @param boolean $moresecure   is more secure mode?
     */
    function generatekey_once($name, $moresecure=false)
    {
        $this->name = $name;
        if (ifnull($this->CI->session->userdata("encryptBAPkey".$this->name), "") == "")
        {
            $this->generatekey($name, $moresecure);
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Generate key, then all information save to session with given name
     * @param string $name      name of the encryption in session. IMPORTANT: if use moresecure mode, 1 name for 1 encrypt-decrypt, because every encrypt will generate unique tag
     * @param boolean $moresecure   is more secure mode?
     */
    function generatekey($name, $moresecure=false)
    {
        $this->moresecure   = $moresecure;
        //$key should have been previously generated in a cryptographically safe way, like openssl_random_pseudo_bytes
        $this->key["cipher"] = ($this->moresecure) ? "aes-128-gcm" : "aes-128-cbc";
        if (in_array($this->key["cipher"], openssl_get_cipher_methods()))
        {
            $this->randomkey(10);
            $this->randomiv(openssl_cipher_iv_length($this->key["cipher"]));
            $this->key["options"]   = OPENSSL_RAW_DATA;
            $this->name             = $name;
            //store $cipher, $iv, and $tag for decryption later
            $this->savekey();
        }
    }
    
    // --------------------------------------------------------------------

    /**
     * Encrypt given plain text, with key from session with given name. Call generatekey() first
     * @param string $plaintext text to encrypt
     * @param string $type      json or default
     * @return string encrypted text
     */
    private function encryption_($plaintext, $type)
    {
        $this->loadkey();
        $ciphertext = "";
        //$key should have been previously generated in a cryptographically safe way, like openssl_random_pseudo_bytes
        if (in_array($this->key["cipher"], openssl_get_cipher_methods()))
        {
            if ($this->moresecure)
            {
                $ciphertext         = openssl_encrypt($plaintext, $this->key["cipher"], $this->key["key"], $this->key["options"], $this->key["iv"], $this->key["tag"]);
            }
            else
            {
                $ciphertext         = openssl_encrypt($plaintext, $this->key["cipher"], $this->key["key"], $this->key["options"], $this->key["iv"]);
            }
        }
        if ($this->debug)
        {
            echo "<br/>Encrypt ".$plaintext." to ".$ciphertext;
        }
        $this->key["type"] = $type;
        $this->savekey();
        return $ciphertext;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Encrypt given plain text, with key from session with given name. Call generatekey() first
     * @param string $plaintext text to encrypt
     * @param string $type      json or default
     * @return string encrypted text
     */
    function encrypt($plaintext, $type = "")
    {
        return base64_encode($this->encryption_($plaintext, $type));
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Encrypt given plain text, with key from session with given name. Call generatekey() first. URL SAFE
     * @param string $plaintext text to encrypt
     * @param string $type      json or default
     * @return string encrypted text
     */
    function encrypt_urlsafe($plaintext, $type = "")
    {
        //https://stackoverflow.com/questions/10482712/how-url-encrypt-and-decrypt-in-codeigniter-every-refresh-encrypted-value-change
        switch ($type)
        {
            case "json": return urlencode(str_replace(array('+','/','='), array('-','_',''), $this->encrypt(json_encode($plaintext), $type)));
            default: return urlencode(str_replace(array('+','/','='), array('-','_',''), $this->encrypt($plaintext, $type)));
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Decrypt cipher text, all information loaded from session with given name
     * @param string $name          name of the encryption in session. IMPORTANT: if use moresecure mode, 1 name for 1 encrypt-decrypt, because every encrypt will generate unique tag
     * @param string $ciphertext    text to decrypt
     * @return string original text
     */
    private function decryption_($name, $ciphertext)
    {
        $plaintext      = "";
        $this->name     = $name;
        $this->loadkey();
        if (in_array($this->key["cipher"], openssl_get_cipher_methods()))
        {
            if ($this->moresecure)
            {
                $plaintext = openssl_decrypt($ciphertext, $this->key["cipher"], $this->key["key"], $this->key["options"], $this->key["iv"], $this->key["tag"]);
            }
            else
            {
                $plaintext = openssl_decrypt($ciphertext, $this->key["cipher"], $this->key["key"], $this->key["options"], $this->key["iv"]);
            }
            if ($this->debug)
            {
                echo "<br/>Decrypt ".$ciphertext." to ".$plaintext;
            }
        }
        return $plaintext;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Decrypt cipher text, all information loaded from session with given name
     * @param string $name          name of the encryption in session. IMPORTANT: if use moresecure mode, 1 name for 1 encrypt-decrypt, because every encrypt will generate unique tag
     * @param string $ciphertext    text to decrypt
     * @return string original text
     */
    function decrypt($name, $ciphertext)
    {
        return $this->decryption_($name, base64_decode($ciphertext));
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Decrypt cipher text, all information loaded from session with given name. URL SAFE
     * @param string $name          name of the encryption in session. IMPORTANT: if use moresecure mode, 1 name for 1 encrypt-decrypt, because every encrypt will generate unique tag
     * @param string $ciphertext    text to decrypt
     * @param string $type          json or default
     * @return string original text
     */
    function decrypt_urlsafe($name, $ciphertext)
    {
        $ciphertext_ = str_replace(array('-','_'),array('+','/'), urldecode($ciphertext));
        $mod4 = strlen($ciphertext_) % 4;
        if ($mod4) 
        {
            $ciphertext_ .= substr('====', $mod4);
        }
        switch ($this->key["type"])
        {
            case "json": return json_decode($this->decrypt($name, $ciphertext_));
            default: return $this->decrypt($name, $ciphertext_);
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Save all information to session
     */
    private function savekey()
    {
        $this->CI->session->set_userdata("encryptBAPkey".$this->name, $this->serialize());
        if ($this->debug)
        {
            echo "<br/>Save Key Debug";
            echo "<br/>Log Process...: ";
            $this->print_();
            echo "<br/>Data yang akan disimpan ke Sesi: ".$this->serialize();
            echo "<br/>Session data..: ".$this->CI->session->userdata("encryptBAPkey".$this->name);
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Load all information from session
     */
    private function loadkey()
    {
        $this->unserialize($this->CI->session->userdata("encryptBAPkey".$this->name));
        if ($this->debug)
        {
            echo "<br/>Load Key Debug";
            echo "<br/>Log Process...: ";
            $this->print_();
            echo "<br/>Session data..: ".$this->CI->session->userdata("encryptBAPkey".$this->name);
        }
    }
    
    /**
     * Allow to debug, print information needed
     */
    function debugmode_on()
    {
        $this->debug = true;
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
        $this->key["key"]  = $this->randchar($length);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Iv built from random chars
     * @param int $length   random iv length
     */
    function randomiv($length)
    {
        $this->key["iv"]   = $this->randchar($length);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Format information before stored to session
     * @return string   base64 serialize information
     */
    function serialize()
    {
        return serialize(array("cipher"=>$this->key["cipher"], "key"=>$this->key["key"], "options"=>$this->key["options"], "iv"=>$this->key["iv"], "tag"=>base64_encode($this->key["tag"]), "type"=>$this->key["type"]));
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Load information from session
     * @param string $sessionvalue  information from session
     */
    function unserialize($sessionvalue)
    {
        $this->key  = (array) unserialize($sessionvalue);
        $this->key["tag"] = base64_decode($this->key["tag"]);
        if ($this->debug)
        {
            echo "<br/>Unserialize value: ";
            print_r($this->key);
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Print all information to screen
     */
    function print_()
    {
        echo "cipher:".$this->key["cipher"]."|key:".$this->key["key"]."|options:".$this->key["options"]."|iv:".$this->key["iv"]."|tag:".$this->key["tag"]."|type:".$this->key["type"];
    }
}
/**
EXAMPLE
//if you load from application/library
//$this->CI =& get_instance();
//$this->CI->load->library('EncryptBAP');
//$this->CI->encryptbap->generatekey($name);
//$enc = $this->CI->encryptbap->encrypt($name, "sometext");
//$this->CI->encryptbap->decrypt($name, $enc);
$this->load->library('EncryptBAP');
$this->encryptbap->generatekey($name);
$enc = $this->encryptbap->encrypt("sometext");
$enc = $this->encryptbap->decrypt($name, $enc);
 
IMPORTANT:
if use moresecure mode, 1 name for 1 encrypt-decrypt, because every encrypt will generate unique tag
 */
