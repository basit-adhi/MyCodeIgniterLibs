<?php
defined('BASEPATH') OR exit('No direct script access allowed');

//copy define to helpers
//defined('BPJS_PESERTA') OR define('BPJS_PESERTA', 1);
//defined('BPJS_RUJUKAN') OR define('BPJS_RUJUKAN', 2);
//defined('BPJS_SEP') OR define('BPJS_SEP', 3);
//defined('BPJS_APLICARE') OR define('BPJS_APLICARE', 4);
//
//defined('JENISAPLIKASI_VCLAIM') OR define('JENISAPLIKASI_VCLAIM', 1);
//defined('JENISAPLIKASI_APLICARE') OR define('JENISAPLIKASI_APLICARE', 2);
//
//defined('JENISCONSID_DEVELOPMENT') OR define('JENISCONSID_DEVELOPMENT', 1);
//defined('JENISCONSID_PRODUCTION') OR define('JENISCONSID_PRODUCTION', 2);
/**
 * Bpjsbap.php
 * <br />BPJS Class / Indonesian National Health Insurance
 * <br />All documentation will be in Bahasa Indonesia
 * 
 * @author Basit Adhi Prabowo, S.T. <basit@unisayogya.ac.id>
 * @access public
 * @link https://github.com/basit-adhi/MyCodeIgniterLibs/blob/master/libraries/Bpjsbap.php
 */
class Bpjsbap
{
    /**
     *
     * @var type merupakan kode consumer (pengakses web-service). Kode ini akan diberikan oleh BPJS Kesehatan
     */
    private $Xconsid = 123456; //masukkan kode yang diberikan oleh BPJS
    /**
     *
     * @var type informasi Consumer Secret, hanya disimpan oleh service consumer. Tidak dikirim ke server web-service, hal ini untuk menjaga pengamanan yang lebih baik. Sedangkan kebutuhan Consumer Secret ini adalah untuk men-generate Signature (X-signature).
     */
    private $Xconssecret = 'aZ12345678'; //masukkan password yang diberikan oleh BPJS
    /**
     *
     * @var type merupakan kode PPK yang diberikan oleh BPJS Kesehatan
     */
    private $Xkodeppk    = "00000R0000";
    private $norujukan  = "";
    private $response;
    private $idrequest;
    private $jenisaplikasi;
    private $jenisconsid;
    private $url;

    function createSignature($requestParameter)
    {
        //menghitung timestamp
        date_default_timezone_set('UTC');
        $tStamp             = strval(time()-strtotime('1970-01-01 00:00:00'));
        //menghitung tanda tangan dengan melakukan hash terhadap salt dengan kunci rahasia sebagai kunci
        $signature          = base64_encode(hash_hmac('sha256', $this->Xconsid."&".$tStamp, $this->Xconssecret, true));
 
        if ($this->jenisconsid == JENISCONSID_DEVELOPMENT)
        {
            $this->url = ($this->jenisaplikasi == JENISAPLIKASI_VCLAIM) ? 'https://dvlp.bpjs-kesehatan.go.id/vclaim-rest' : 'https://dvlp.bpjs-kesehatan.go.id:8888/aplicaresws';
        }
        else if ($this->jenisconsid == JENISCONSID_PRODUCTION)
        {
            $this->url = ($this->jenisaplikasi == JENISAPLIKASI_VCLAIM) ? 'https://new-api.bpjs-kesehatan.go.id:8080/new-vclaim-rest/' : 'http://api.bpjs-kesehatan.go.id/aplicaresws';
        }
        $headers = array(   "Accept: application/json","X-cons-id: ".$this->Xconsid, 
                            "X-timestamp: ".$tStamp, 
                            "X-signature: ".$signature
                        );
        $ch = curl_init($this->url.$requestParameter);
        echo $this->url.$requestParameter;
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $data = curl_exec($ch);
        if (empty($data))
        {
            $data = curl_error($ch);
        }
        curl_close($ch);
        return json_decode($data);
    }
    
    //vclaim
    function requestPropinsi()
    {
        print_r($this->createSignature("/referensi/propinsi"));
    }
    
    function requestPeserta($nobpjs)
    {
        $this->idrequest        = BPJS_PESERTA;
        $this->jenisaplikasi    = JENISAPLIKASI_VCLAIM;
        $this->response         = $this->createSignature("/Peserta/nokartu/$nobpjs/tglSEP/".date('Y-m-d'));
        $this->norujukan        = "";
    }
    
    function requestRujukan($norujukan)
    {
        $this->idrequest        = BPJS_RUJUKAN;
        $this->jenisaplikasi    = JENISAPLIKASI_VCLAIM;
        $this->response         = $this->createSignature("/Rujukan/$norujukan");
        $this->norujukan        = $norujukan;
    }
    
    function requestRujukanMulti($nokartu)
    {
        $this->idrequest        = BPJS_RUJUKAN;
        $this->jenisaplikasi    = JENISAPLIKASI_VCLAIM;
        $this->response         = $this->createSignature("/Rujukan/List/Peserta/$nokartu");
        $this->norujukan        = "";
        print_r($this->response);
    }
    
    function requestSEP($nosep)
    {
        $this->idrequest        = BPJS_SEP;
        $this->jenisaplikasi    = JENISAPLIKASI_VCLAIM;
        $this->response         = $this->createSignature("/SEP/$nosep");
        if ($this->response->metaData->code == 200 && $this->idrequest == BPJS_SEP)
        {
            $this->norujukan    = $this->response->response->noRujukan;
        }
    }
    
    function DUMMY_requestPeserta($nobpjs)
    {
        $this->idrequest        = BPJS_PESERTA;
        $this->jenisaplikasi    = JENISAPLIKASI_VCLAIM;
        if (substr($nobpjs, 0, 10) == '0011223344')
        {
            $kelas  = [["id" => 1, "nama" => "Kelas I"], ["id" => 2, "nama" => "Kelas II"], ["id" => 3, "nama" => "Kelas III"]];
            $pkelas = $kelas[array_rand($kelas, 1)];
            $jk     = ["L", "P"];
            $pjk    = $jk[array_rand($jk)];
            $this->response = json_decode('{"metaData":{"code":"200","message":"OK"},"response":{"peserta":{"cob":{"nmAsuransi":null,"noAsuransi":null,"tglTAT":null,"tglTMT":null},"hakKelas":{"keterangan":"'.$pkelas["nama"].'","kode":"'.$pkelas["id"].'"},"informasi":{"dinsos":null,"noSKTM":null,"prolanisPRB":null},"jenisPeserta":{"keterangan":"PEGAWAI SWASTA","kode":"13"},"mr":{"noMR":null,"noTelepon":null},"nama":"DUMMY'.$nobpjs.'","nik":"3311022033440001","noKartu":"'.$nobpjs.'","pisa":"1","provUmum":{"kdProvider":"0138U020","nmProvider":"KPRJ PALA MEDIKA"},"sex":"'.$pjk.'","statusPeserta":{"keterangan":"AKTIF","kode":"0"},"tglCetakKartu":"2016-02-12","tglLahir":"1981-10-10","tglTAT":"2014-12-31","tglTMT":"2008-10-01","umur":{"umurSaatPelayanan":"35 tahun ,1 bulan ,11 hari","umurSekarang":"35 tahun ,2 bulan ,10 hari"}}}}');
        }
        else
        {
            $this->response = json_decode('{"metaData":{"code":"401","message":"ERROR"}}');
        }
    }
    
    function DUMMY_requestRujukan($norujukan)
    {
        $this->idrequest        = BPJS_RUJUKAN;
        $this->jenisaplikasi    = JENISAPLIKASI_VCLAIM;
        if (substr($norujukan, 0, 13) == '001122334455Y')
        {
            $tglkunjungan   = date('Y-m-d');
            $nobpjstercatat = ['001122334455','001122334456','001122334457','001122334458'];
            $nobpjs         = $nobpjstercatat[array_rand($nobpjstercatat)];
            $this->response = json_decode('{"metaData":{"code":"200","message":"OK"},"response":{"rujukan":{"diagnosa":{"kode":"N40","nama":"Hyperplasia of prostate"},"keluhan":"kencing tidak puas","noKunjungan":"'.$norujukan.'","pelayanan":{"kode":"2","nama":"Rawat Jalan"},"peserta":{"cob":{"nmAsuransi":null,"noAsuransi":null,"tglTAT":null,"tglTMT":null},"hakKelas":{"keterangan":"KELAS I","kode":"1"},"informasi":{"dinsos":null,"noSKTM":null,"prolanisPRB":null},"jenisPeserta":{"keterangan":"PENERIMA PENSIUN PNS","kode":"15"},"mr":{"noMR":"298036","noTelepon":null},"nama":"MUSDIWAR,BA","nik":null,"noKartu":"'.$nobpjs.'","pisa":"2","provUmum":{"kdProvider":"03010701","nmProvider":"SITEBA"},"sex":"L","statusPeserta":{"keterangan":"AKTIF","kode":"0"},"tglCetakKartu":"2017-11-13","tglLahir":"1938-08-31","tglTAT":"2038-08-31","tglTMT":"1996-08-20","umur":{"umurSaatPelayanan":"78 tahun ,6 bulan ,6 hari","umurSekarang":"79 tahun ,3 bulan ,18 hari"}},"poliRujukan":{"kode":"URO","nama":"UROLOGI"},"provPerujuk":{"kode":"03010701","nama":"SITEBA"},"tglKunjungan":"'.$tglkunjungan.'"}}}');
        }
        else
        {
            $this->response = json_decode('{"metaData":{"code":"401","message":"ERROR"}}');
        }
    }
     
    function getInfoPrimer()
    {
        if ($this->response->metaData->code == 200 && ($this->idrequest == BPJS_PESERTA || $this->idrequest == BPJS_RUJUKAN || $this->idrequest == BPJS_SEP))
        {
            switch ($this->idrequest)
            {
                case BPJS_PESERTA:
                    $peserta = $this->response->response->peserta; break;
                case BPJS_RUJUKAN:
                    $peserta = $this->response->response->rujukan->peserta; break;
                case BPJS_SEP:
                    $this->requestRujukan($this->norujukan);
                    return $this->getInfoPrimer();
                default:
                    return array();
            }
            return  [   'nik'           => $peserta->nik,
                        'namaLengkap'   => $peserta->nama,
                        'tglLahir'      => $peserta->tglLahir,
                        'jenisKelamin'  => $peserta->sex,
                        'noTelepon'     => ($this->idrequest == BPJS_SEP) ? "" : $peserta->mr->noTelepon
                    ];
        }
        else
        {
            return array();
        }
    }
    
    function getInfoSekunder()
    {
        if ($this->response->metaData->code == 200 && ($this->idrequest == BPJS_PESERTA || $this->idrequest == BPJS_RUJUKAN || $this->idrequest == BPJS_SEP))
        {
            switch ($this->idrequest)
            {
                case BPJS_PESERTA:
                    $peserta = $this->response->response->peserta; break;
                case BPJS_RUJUKAN:
                    $peserta = $this->response->response->rujukan->peserta; break;
                case BPJS_SEP:
                    $this->requestRujukan($this->norujukan);
                    return $this->getInfoSekunder();
                default:
                    return array();
            }
            return  [   'jenisPeserta'  => $peserta->jenisPeserta->kode,
                        'hakKelas'      => $peserta->hakKelas->kode,
                        'informasi'     => trim(str_replace(',,', '', implode(',', (array) $peserta->informasi))),
                        'tglCetakKartu' => $peserta->tglCetakKartu,
                        'statusPeserta' => $peserta->statusPeserta->kode,
                        'norujukan'     => $this->norujukan 
                    ];
        }
        else
        {
            return array();
        }
    }
    
    function getInfoRujukan()
    {
        if ($this->response->metaData->code == 200 && ($this->idrequest == BPJS_RUJUKAN))
        {
            $rujukan = $this->response->response->rujukan;
            return  $rujukan->poliRujukan->nama." [".$rujukan->diagnosa->kode."] ".$rujukan->diagnosa->nama." - ".$rujukan->keluhan." pada ".$rujukan->tglKunjungan;
}
        else
        {
            return array();
        }
    }
    
    //aplicares
    function requestKelas($jenisconsid)
    {
        $this->idrequest        = BPJS_APLICARE;
        $this->jenisaplikasi    = JENISAPLIKASI_APLICARE;
        $this->jenisconsid      = $jenisconsid;
        $this->response         = json_decode(json_encode($this->createSignature("/rest/ref/kelas")), true);
        if ($this->response["metadata"]["code"] == 200 && $this->idrequest == BPJS_APLICARE)
        {
            return  $this->response["list"];
        }
    }
    
    function requestBedlist($jenisconsid)
    {
        $this->idrequest        = BPJS_APLICARE;
        $this->jenisaplikasi    = JENISAPLIKASI_APLICARE;
        $this->jenisconsid      = $jenisconsid;
        $this->response         = json_decode(json_encode($this->createSignature("/rest/bed/read/".$this->Xkodeppk."/1/10000")), true);
        $code                   = array_key_exists("code", $this->response["metadata"]) ? $this->response["metadata"]["code"] : (array_key_exists("Code", $this->response) ? $this->response["Code"] : 0); 
        if ($code == 1 && $this->idrequest == BPJS_APLICARE)
        {
            return $this->response["response"]["list"];
        }
        else
        {
            return $this->response["metadata"]["message"];
        }
    }
}
