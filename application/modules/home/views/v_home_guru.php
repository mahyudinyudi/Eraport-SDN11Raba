<link href="<?php echo base_url(); ?>aset/css/mystyle.css" rel="stylesheet" />

<div class="row">
    <center>
        <?php 
            $wali_kelas = $this->session->userdata('app_rapot_walikelas');
            $is_wali = $wali_kelas['is_wali'];
            ?>
        <h3 class="title"><b>Selamat datang di Aplikasi E-Raport Online SDN 11 Rantau Bayur</b><br>
            Anda Login sebagai : <?php echo $this->session->userdata('app_rapot_nama'); ?><br><?php echo $wali = $is_wali == true ? "Wali Kelas : ".$wali_kelas['nama_walikelas'] : ""; ?></h3>
        <h4></h4>
    </center>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-red" ><i class="fa fa-calendar-check-o" style="margin-right: 20%;color: #fff"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Tahun Akademik Aktif</span>
              <span class="info-box-number"><?=$tasm?></span>
            </div>
        </div><!-- /.info-box-content -->
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-blue" ><i class="fa fa-clipboard" style="margin-right: 20%;color: #fff"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Total Mapel Diampu</span>
              <span class="info-box-number"><?=$mapel_diampuh?> Mapel</span>
            </div>
        </div><!-- /.info-box-content -->
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-red" ><i class="fa fa-users" style="margin-right: 25%;color: #fff"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Total Siswa</span>
              <span class="info-box-number" style="font-size: 20px;">LK = <?=$stat_kelas['jmlk_l']?>, PR = <?=$stat_kelas['jmlk_p']?></span>
            </div>
        </div><!-- /.info-box-content -->
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-blue" ><i class="fa fa-calendar" style="margin-right: 25%;color: #fff"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Tgl Bagi Raport</span>
              <span class="info-box-number"><?=$bagi_raport?></span>
            </div>
        </div><!-- /.info-box-content -->
    </div>

</div>

<div class="row">
    
    <div class="col-md-12">
        <div class="alert alert-warning" style="color: #000">
            <b>Petunjuk : </b><br>
            Menu ini digunakan untuk menginput nilai pada setiap masing-masing mata pelajaran diampu. Silakan klik menu <b><i>Nilai Pengetahuan</i></b> untuk menginput nilai pengetahuan, dan <b><i>Nilai Keterampilan</i></b> untuk menginput nilai keterampilan.
        </div>
    </div>
    <?php 
    if (!empty($list_mapelkelas)) {
        foreach ($list_mapelkelas as $mk) {
    ?>
    <div class="col-md-4">
        <div class="card">
            <div class="header">
                <h4 class="title"><?php echo $mk['nmmapel']." - ".$mk['nmkelas']; ?>    
                <!--
                <a href="#" class="btn btn-success btn-xs pull-right"><i class="fa fa-check"></i> </a>
                -->
                </h4>
            </div>
            <div class="content">
                <ul class="list-group">
                    <li class="list-group-item"><a href="<?php echo base_url()."n_pengetahuan/index/".$mk['id']; ?>"><i class="fa fa-chevron-right"></i>  Nilai Pengetahuan</a> <!--<a href="#" class="badge pull-right"><i class="fa fa-print"></i> --></li>
                    <li class="list-group-item"><a href="<?php echo base_url()."n_keterampilan/index/".$mk['id']; ?>"><i class="fa fa-chevron-right"></i>  Nilai Keterampilan</a></li><!-- 
                    <li class="list-group-item"><a href="<?php echo base_url()."view_mapel/cetak_absensi/".$mk['id']; ?>" target="_blank"><i class="fa fa-chevron-right"></i>  Cetak Presensi</a></li> -->
                    <!--
                    <?php 
                    if ($mk['is_sikap'] == "1") {
                    ?>
                    <li class="list-group-item"><a href="<?php echo base_url()."n_sikap_sp/index/".$mk['id']; ?>">Nilai Sikap Spritual</a></li>
                    <li class="list-group-item"><a href="<?php echo base_url()."n_sikap_so/index/".$mk['id']; ?>">Nilai Sikap Sosial</a></li>
                    <?php 
                    }
                    ?>
                    -->
                </ul>
            </div>
        </div>
    </div>
    <?php 
        }
    } else {
        echo '<div class="alert alert-info">Belum ada mapel yang diampu..</div>';
    }
    ?>
</div>
