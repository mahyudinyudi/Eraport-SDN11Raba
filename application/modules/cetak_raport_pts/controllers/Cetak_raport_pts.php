<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cetak_raport_pts extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->sespre = $this->config->item('session_name_prefix');

        $this->d['admlevel'] = $this->session->userdata($this->sespre.'level');
        $this->d['admkonid'] = $this->session->userdata($this->sespre.'konid');
        $this->d['url'] = "cetak_raport_pts";

        $get_tasm = $this->db->query("SELECT tahun, nama_kepsek, nip_kepsek, tgl_raport FROM tahun WHERE aktif = 'Y'")->row_array();
        $this->d['tasm'] = $get_tasm['tahun'];
        $this->d['ta'] = substr($get_tasm['tahun'],0,4);

        $this->d['wk'] = $this->session->userdata('app_rapot_walikelas');   
    }

    public function sampul1($id_siswa) {
        $d['ds'] = $this->db->query("SELECT nama, nis, nisn FROM m_siswa WHERE id = '$id_siswa'")->row_array();

        $this->load->view('cetak_sampul1', $d);

    }

    public function sampul2($id_siswa) {
        $d = null;

        $this->load->view('cetak_sampul2', $d);
    }

    public function sampul4($id_siswa) {
        $d['ds'] = $this->db->query("SELECT * FROM m_siswa WHERE id = '$id_siswa'")->row_array();
        $d['da'] = $this->db->query("SELECT * FROM tahun WHERE aktif = 'Y'")->row_array();

        $this->load->view('cetak_sampul4', $d);

    }

    public function prestasi_catatan($id_siswa,$tasm) {
        //$tasm = substr($tasm, 0, 4);
        $q_prestasi = $this->db->query("SELECT 
                                    a.*
                                    FROM t_prestasi a 
                                    LEFT JOIN m_siswa c ON a.id_siswa = c.id
                                    WHERE a.id_siswa = $id_siswa AND a.ta = '$tasm'")->result_array();
                                    
        //echo $this->db->last_query();
        //exit;


        $q_catatan = $this->db->query("SELECT 
                                    a.*
                                    FROM t_naikkelas a 
                                    WHERE a.id_siswa = $id_siswa AND a.ta = '$tasm'")->row_array();
                                    
                                    // echo $this->db->last_query();
                                    // exit;
                                    
        $d['prestasi'] = $q_prestasi;
        $d['catatan'] = $q_catatan;

        $this->load->view('cetak_prestasi', $d);
    }

    public function cetak($id_siswa,$tasm) {
        $d = array();
        
        
        $d['semester'] = substr($tasm, 4, 1);
        $d['ta'] = (substr($tasm, 0, 4))."/".(substr($tasm, 0, 4)+1);
        
        $siswa = $this->db->query("SELECT 
                                    a.nama, a.nis, a.nisn, c.tingkat, c.id idkelas
                                    FROM m_siswa a
                                    LEFT JOIN t_kelas_siswa b ON a.id = b.id_siswa
                                    LEFT JOIN m_kelas c ON b.id_kelas = c.id
                                    WHERE a.id = $id_siswa AND b.ta = '".$d['ta']."'")->row_array();
        
        $d['det_siswa'] = $siswa;
        
        $d['wali_kelas'] = $this->db->query("SELECT 
                                a.*, b.nama nmguru, b.nip, 
                                c.tingkat, c.nama nmkelas
                                FROM t_walikelas a 
                                INNER JOIN m_guru b ON a.id_guru = b.id 
                                INNER JOIN m_kelas c ON a.id_kelas = c.id
                                WHERE a.id_kelas = '".$d['det_siswa']['idkelas']."' AND a.tasm = '".$this->d['ta']."'")->row_array();
    
        // Start NILAI PENGETAHUAN //
        $ambil_np = $this->db->query("SELECT 
                                    c.id idmapel, c.kkm, a.tasm, c.kd_singkat, a.jenis, if(a.jenis='h',CONCAT(a.nilai,'//',d.nama_kd),a.nilai) nilai
                                    FROM t_nilai a
                                    INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                                    INNER JOIN m_mapel c ON b.id_mapel = c.id
                                    INNER JOIN t_mapel_kd d ON a.id_mapel_kd = d.id
                                    WHERE a.id_siswa = $id_siswa
                                    AND a.tasm = '".$tasm."'")->result_array();
       

        $ambil_np_submp = $this->db->query("SELECT 
                                    b.id_mapel, c.kd_singkat
                                    FROM t_nilai a
                                    INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                                    INNER JOIN m_mapel c ON b.id_mapel = c.id
                                    WHERE a.id_siswa = $id_siswa AND a.tasm = '".$tasm."'
                                    GROUP BY b.id_mapel")->result_array();

        $array1 = array();

        foreach ($ambil_np_submp as $a1) {
            $array1[$a1['id_mapel']] = array();   
        }

        foreach ($ambil_np as $a2) {
            $idx = $a2['idmapel'];
            $kkmx = $a2['kkm'];

            //$pc_nilai = explode("//", $a2['nilai']);

            if ($a2['jenis'] == "h") {
                $array1[$idx]['h'][] = $a2['nilai'];
            } else if ($a2['jenis'] == "t") {
                $array1[$idx]['t'] = $a2['nilai'];
            } else if ($a2['jenis'] == "a") {
                $array1[$idx]['a'] = $a2['nilai'];
            }
        }

        //echo var_dump($array1);

        //$bobot_h = $this->config->item('pnp_h');
        $bobot_t = $this->config->item('pnp_t');
       // $bobot_a = $this->config->item('pnp_a');

        $jml_bobot =$bobot_t;

        //MULAI HITUNG..
        $nilai_pengetahuan = array();
        foreach ($array1 as $k => $v) {
            
           
            $desk = array();

           
            $txt_desk = array();
            foreach ($desk as $r => $s) {
                $txt_desk[] = $r." pada: ".implode(", ", $s);
            }

            $__tengah = empty($array1[$k]['t']) ? 0 : $array1[$k]['t'];
          //  $__akhir = empty($array1[$k]['a']) ? 0 : $array1[$k]['a'];

            $_np = round((($bobot_t/$jml_bobot) * $__tengah),0);
            

            $nilai_pengetahuan[$k]['nilai'] = number_format($_np); 
            $nilai_pengetahuan[$k]['predikat'] = nilai_huruf($kkmx, $_np); 
            $nilai_pengetahuan[$k]['desk'] = implode("; ", $txt_desk); 
        }

        //echo j($nilai_pengetahuan);
        $d['nilai_pengetahuan'] = $nilai_pengetahuan;
        // END Nilai PENGETAHUAN
        $d['nilai_utama'] = '';


            $arr_huruf = array("a","b","c","d","e");

            $d['nilai_utama'] .= '<tr><td colspan="9"><b>KELOMPOK A</b></td></tr>';
            $no = 1;


            //foreach ($q_mapel as $m) {
            //PAI kelompok A
            if ($this->config->item('is_kemenag') == TRUE) {    
                $d['nilai_utama'] .= '<tr><td class="ctr">'.$no.'</td><td colspan="6">Pendidikan Agama Islam</td></tr>';
                $q_mapel = $this->db->query("SELECT * FROM m_mapel WHERE kelompok = 'A' AND tambahan_sub = 'PAI'")->result_array();

                foreach ($q_mapel as $i=>$m) {
                    $kkmx = $m['kkm'];

                    $idx = $m['id'];
                    $npa = empty($nilai_pengetahuan[$idx]['nilai']) ? "-" : $nilai_pengetahuan[$idx]['nilai'];
                    $npp = empty($nilai_pengetahuan[$idx]['predikat']) ? "-" : $nilai_pengetahuan[$idx]['predikat'];

                    if($npa >= $kkmx){
                        $predikatx = "sudah tuntas";
                    }else{
                        $predikatx = "belum tuntas";
                    }

                    $npd = "Capaian kompetensi Ananda ".$siswa['nama']." ".$predikatx." dengan predikat ".nilai_pre($kkmx, $npa);
                   

                    
                    $d['nilai_utama'] .= '
                                        <tr>
                                            <td class="ctr"></td>
                                            <td>'.$arr_huruf[$i].'. '.$m['nama'].'</td>
                                            <td class="ctr">'.$m['kkm'].'</td>
                                            <td class="ctr">'.$npa.'</td>
                                            <td class="ctr">'.$npp.'</td>
                                            <td class="font_kecil">'.$npd.'</td>
                                        </tr>';
                }
            }

            $no++;

            //no pai kelompok A
            $q_mapel = $this->db->query("SELECT * FROM m_mapel WHERE kelompok = 'A' AND tambahan_sub = 'NO'")->result_array();

            foreach ($q_mapel as $i=>$m) {

                $kkmx = $m['kkm'];

                $idx = $m['id'];
              $npa = empty($nilai_pengetahuan[$idx]['nilai']) ? "-" : $nilai_pengetahuan[$idx]['nilai'];
                    $npp = empty($nilai_pengetahuan[$idx]['predikat']) ? "-" : $nilai_pengetahuan[$idx]['predikat'];

                    if($npa >= $kkmx){
                        $predikatx = "sudah tuntas";
                    }else{
                        $predikatx = "belum tuntas";
                    }

                    $npd = "Capaian kompetensi Ananda ".$siswa['nama']." ".$predikatx." dengan predikat ".nilai_pre($kkmx, $npa);

                $d['nilai_utama'] .= '
                                    <tr>
                                        <td class="ctr">'.$no++.'</td>
                                        <td>'.$m['nama'].'</td>
                                        <td class="ctr">'.$m['kkm'].'</td>
                                        <td class="ctr">'.$npa.'</td>
                                        <td class="ctr">'.$npp.'</td>
                                        <td class="font_kecil">'.$npd.'</td>
                                    </tr>';
            }

            //no pai kelompok B
            $d['nilai_utama'] .= '<tr><td colspan="6"><b>KELOMPOK B</b></td></tr>';
            $q_mapel = $this->db->query("SELECT * FROM m_mapel WHERE kelompok = 'B' AND tambahan_sub = 'NO'")->result_array();

            foreach ($q_mapel as $i=>$m) {
                $idx = $m['id'];
                $kkmx = $m['kkm'];

               $npa = empty($nilai_pengetahuan[$idx]['nilai']) ? "-" : $nilai_pengetahuan[$idx]['nilai'];
                    $npp = empty($nilai_pengetahuan[$idx]['predikat']) ? "-" : $nilai_pengetahuan[$idx]['predikat'];

                    if($npa >= $kkmx){
                        $predikatx = "sudah tuntas";
                    }else{
                        $predikatx = "belum tuntas";
                    }

                    $npd = "Capaian kompetensi Ananda ".$siswa['nama']." ".$predikatx." dengan predikat ".nilai_pre($kkmx, $npa);
                $d['nilai_utama'] .= '
                                    <tr>
                                        <td class="ctr">'.$no++.'</td>
                                        <td>'.$m['nama'].'</td>
                                        <td class="ctr">'.$m['kkm'].'</td>
                                        <td class="ctr">'.$npa.'</td>
                                        <td class="ctr">'.$npp.'</td>
                                        <td class="font_kecil">'.$npd.'</td>
                                    </tr>';
            }

            $d['nilai_utama'] .= '<tr><td class="ctr">'.$no.'</td><td colspan="6">Muatan Lokal</td></tr>';
                $q_mapel = $this->db->query("SELECT * FROM m_mapel WHERE kelompok = 'B' AND tambahan_sub = 'MULOK'")->result_array();

            foreach ($q_mapel as $i=>$m) {
                $idx = $m['id'];
                $kkmx = $m['kkm'];

               $npa = empty($nilai_pengetahuan[$idx]['nilai']) ? "-" : $nilai_pengetahuan[$idx]['nilai'];
                    $npp = empty($nilai_pengetahuan[$idx]['predikat']) ? "-" : $nilai_pengetahuan[$idx]['predikat'];

                    if($npa >= $kkmx){
                        $predikatx = "sudah tuntas";
                    }else{
                        $predikatx = "belum tuntas";
                    }

                    $npd = "Capaian kompetensi Ananda ".$siswa['nama']." ".$predikatx." dengan predikat ".nilai_pre($kkmx, $npa);

                $d['nilai_utama'] .= '
                                    <tr>
                                        <td class="ctr"></td>
                                        <td>'.$arr_huruf[$i].'. '.$m['nama'].'</td>
                                        <td class="ctr">'.$m['kkm'].'</td>
                                        <td class="ctr">'.$npa.'</td>
                                        <td class="ctr">'.$npp.'</td>
                                        <td class="font_kecil">'.$npd.'</td>
                                    </tr>';
            }

            //}
        //}
        $d['det_raport'] = $get_tasm = $this->db->query("SELECT tahun, nama_kepsek, nip_kepsek, tgl_raport, tgl_raport_kelas3 FROM tahun WHERE tahun = '$tasm'")->row_array();
        
        //utk naik kelas atau tidak
        $q_catatan = $this->db->query("SELECT 
                                    a.*
                                    FROM t_naikkelas a 
                                    WHERE a.id_siswa = $id_siswa AND a.ta = '$tasm'")->row_array();
        $d['catatan'] = $q_catatan;

        $this->load->view('cetak_rapot_pts', $d);
    }



    public function index() {

        $wali = $this->session->userdata($this->sespre."walikelas");

        $this->d['siswa_kelas'] = $this->db->query("SELECT 
                                                a.id_siswa, b.nama
                                                FROM t_kelas_siswa a
                                                INNER JOIN m_siswa b ON a.id_siswa = b.id
                                                WHERE a.id_kelas = '".$wali['id_walikelas']."' AND a.ta = '".$this->d['ta']."'")->result_array();

    	$this->d['p'] = "list";
        $this->load->view("template_utama", $this->d);
    }

}