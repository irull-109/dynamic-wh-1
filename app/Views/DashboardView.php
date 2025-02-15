<?= $this->extend('layout/index'); ?>
<?= $this->section('content'); ?>
<?= $this->include('layout/logoutnav'); ?>
<style>
    .card-title {
        text-align: center;
    }

    a {
        color: black;
    }

    a :hover {
        color: azure;
    }

    #card {
        display: flex;
        flex-direction: column;
        overflow: hidden;
        background-color: #24a0ed;
        /* background-color:#198754;  */
        /* background-color:#24a0ed;  */
        width: 100%;
        height: 100%;
    }

    .card-item {
        display: flex;
        width: 32.2%;
        margin: 8px;
    }

    .card-title {
        font-weight: 500;
    }

    #big-card {
        display: flex;
        flex-wrap: wrap;
        flex-direction: row;
        list-style: none;
    }
</style>
<title><?= $title ?></title>
<div class="container-fluid p-3">
    <div class="card">
        <div class="card-body">
            <h1><?= $title ?></h1>
            <div>=====================================</div>
            <h5>Welcome to Dynamic Warehouse System</h5>
        </div>
    </div>
    <div class="card mb-3 mt-2 p-1" id="big-card">
        <?php if (strtolower($level == 'admin') || strtolower($level == 'qc')) : ?>
            <a href="<?= base_url('/scan-ci') ?>" class="card-item">
                <div class="card" id="card">
                    <div class="card-body">
                        <h5 class="card-title">SCAN MASUK</h5>
                    </div>
                </div>
            </a>
        <?php endif; ?>
        <?php if (strtolower($level == 'admin') || strtolower($level == 'delivery')) : ?>
            <a href="<?= base_url('/scan-co') ?>" class="card-item">
                <div class="card" id="card">
                    <div class="card-body">
                        <h5 class="card-title">SCAN KELUAR</h5>
                    </div>
                </div>
            </a>
        <?php endif; ?>
        <?php if (strtolower($level == 'admin') || strtolower($level == 'cs')|| strtolower($level == 'delivery')) : ?>
            <a href="<?= base_url('/adjustment') ?>" class="card-item">
                <div class="card" id="card">
                    <div class="card-body">
                        <h5 class="card-title">SCAN ADJUSTMENT</h5>
                    </div>
                </div>
            </a>
        <?php endif; ?>
        <a href="<?= base_url('/information-rak') ?>" class="card-item">
            <div class="card" id="card">
                <div class="card-body">
                    <h5 class="card-title">INFORMASI RAK</h5>
                </div>
            </div>
        </a>
        <a href="<?= base_url('/history') ?>" class="card-item">
            <div class="card" id="card">
                <div class="card-body">
                    <h5 class="card-title">HISTORY</h5>
                </div>
            </div>
        </a>
        <a href="<?= base_url('/history1') ?>" class="card-item">
            <div class="card" id="card">
                <div class="card-body">
                    <h5 class="card-title">HISTORY2</h5>
                </div>
            </div>
        </a>
        <?php if (strtolower($level == 'admin') || strtolower($level == 'cs')) : ?>
            <a href="<?= base_url('/master-part') ?>" class="card-item">
                <div class="card" id="card">
                    <div class="card-body">
                        <h5 class="card-title">MASTER PART NUMBER</h5>
                    </div>
                </div>
            </a>
        <?php endif; ?>
        <a href="<?= base_url('/master-user') ?>" class="card-item">
            <div class="card" id="card">
                <div class="card-body">
                    <h5 class="card-title">MASTER USER</h5>
                </div>
            </div>
        </a>
        <?php if (strtolower($level == 'admin') || strtolower($level == 'cs')) : ?>
            <a href="<?= base_url('/master_rak') ?>" class="card-item">
                <div class="card" id="card">
                    <div class="card-body">
                        <h5 class="card-title">MASTER RAK</h5>
                    </div>
                </div>
            </a>
        <?php endif; ?>
        <?php if (strtolower($level == 'admin') || strtolower($level == 'cs')) : ?>
            <a href="<?= base_url('/return-part') ?>" class="card-item">
                <div class="card" id="card">
                    <div class="card-body">
                        <h5 class="card-title">RETUR PART</h5>
                    </div>
                </div>
            </a>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection(); ?>