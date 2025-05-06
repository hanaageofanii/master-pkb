<?php

namespace App\Providers;
use App\Models\LegalTkr;
use App\Models\PencairanAkadTkr;
use App\Models\User;
use App\Models\ajb;
use App\Models\ajbPCA;
use App\Models\Audit;
use App\Models\audit_tkr;
use App\Models\dajam;
use App\Models\form_dp;
use App\Models\form_kpr;
use App\Models\form_legal;
use App\Models\form_pajak;
use App\Models\form_ppn;
use App\Models\GCV;
use App\Models\pembayaran;
use App\Models\pencairan_dajam;
use App\Models\PencairanAkad;
use App\Models\pengajuan_dajam;
use App\Models\verifikasi_dajam;
use App\Policies\ajbPCAPolicy;
use App\Policies\form_pajak_pcaPolicy;
use App\Policies\pca;
use App\Policies\pengajuan_dajam_pcaPolicy;
use App\Policies\StokTKR;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use App\Policies\ajbPolicy;
use App\Policies\AjbTkr;
use App\Policies\audit_tkr as PoliciesAudit_tkr;
use App\Policies\AuditPCA;
use App\Policies\AuditPolicy;
use App\Policies\BukuRekonsilPolicy;
use App\Policies\dajamPolicy;
use App\Policies\form_dpPolicy;
use App\Policies\form_kprPolicy;
use App\Policies\form_legal_pcaPolicy;
use App\Policies\form_legalPolicy;
use App\Policies\form_pajakPolicy;
use App\Policies\form_ppn_pcaPolicy;
use App\Policies\form_ppnPolicy;
use App\Policies\FormDpTkr;
use App\Policies\GCVPolicy;
use App\Policies\PajakTkr;
use App\Policies\pembayaranPolicy;
use App\Policies\pencairan_dajam_pca;
use App\Policies\pencairan_dajamPolicy;
use App\Policies\PencairanAkadPolicy;
use App\Policies\PencairanAkadTkr as PoliciesPencairanAkadTkr;
use App\Policies\pengajuan_dajamPolicy;
use App\Policies\PengajuanDajamTkr;
use App\Policies\PpnTkr;
use App\Policies\RekeningPolicy;
use App\Policies\verifikasi_dajamPolicy;
use App\Policies\UserPolicy;
use App\Policies\VerifikasiDajamTkr;
use App\Models\BukuRekonsil;
use App\Models\form_dp_pca;
use App\Models\Rekening;
use App\Models\pengajuan_dajam_pca;
use App\Models\form_kpr_pca;
use App\Models\form_pajak_pca;
use App\Models\form_ppn_pca;
use App\Models\pencairan_akad_pca;
use App\Models\verifikasi_dajam_pca;
use App\Models\form_legal_pca;
use App\Policies\form_dp_pcaPolicy;
use App\Policies\form_kpr_pcaPolicy;
use App\Policies\pencairan_akad_pcaPolicy;
use App\Policies\PencairanDajamTkr;
use App\Policies\verifikasi_dajam_pcaPolicy;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
   protected $policies = [
    // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    \App\Models\User::class => \App\Policies\UserPolicy::class,
    \Spatie\Permission\Models\Role::class => \App\Policies\RolePolicy::class,
    \Spatie\Permission\Models\Permission::class => \App\Policies\PermissionPolicy::class,
    \App\Models\ajb::class => \App\Policies\ajbPolicy::class,
    \App\Models\AjbPCA::class => \App\Policies\ajbPCAPolicy::class,
    \App\Models\AjbTkr::class => \App\Policies\AjbTkr::class,
    \App\Models\Audit::class => \App\Policies\AuditPolicy::class,
    \App\Models\audit_tkr::class => \App\Policies\audit_tkr::class,
    \App\Models\AuditPCA::class => \App\Policies\AuditPCA::class,
    \App\Models\BukuRekonsil::class => \App\Policies\BukuRekonsilPolicy::class,
    \App\Models\form_dp_pca::class => \App\Policies\form_dp_pcaPolicy::class,
    \App\Models\form_dp::class => \App\Policies\form_dpPolicy::class,
    \App\Models\form_kpr_pca::class => \App\Policies\form_kpr_pca::class,
    \App\Models\form_kpr::class => \App\Policies\form_kprPolicy::class,
    \App\Models\form_legal_pca::class => \App\Policies\form_legal_pcaPolicy::class,
    \App\Models\form_legal::class => \App\Policies\form_legalPolicy::class,
    \App\Models\form_pajak_pca::class => \App\Policies\form_pajak_pcaPolicy::class,
    \App\Models\form_ppn_pca::class => \App\Policies\form_ppn_pcaPolicy::class,
    \App\Models\form_ppn::class => \App\Policies\form_ppnPolicy::class,
    \App\Models\FormDpTkr::class => \App\Policies\FormDpTkrPolicy::class,
    \App\Models\FormKprTkr::class => \App\Policies\FormKprTkr::class,
    \App\Models\GCV::class => \App\Policies\GCVPolicy::class,
    \App\Models\LegalTkr::class => \App\Policies\LegalTkr::class,
    \App\Models\PajakTkr::class => \App\Policies\PajakTkr::class,
    \App\Models\Pca::class => \App\Policies\pca::class,
    \App\Models\pencairan_akad_pca::class => \App\Policies\pencairan_akad_pcaPolicy::class,
    \App\Models\pencairan_dajam::class => \App\Policies\pencairan_dajamPolicy::class,
    \App\Models\PencairanAkad::class => \App\Policies\PencairanAkadPolicy::class,
    \App\Models\PencairanAkadTkr::class => \App\Policies\PencairanAkadTkr::class,
    \App\Models\PencairanDajamTkr::class => \App\Policies\PencairanDajamTkrPolicy::class,
    \App\Models\pencairan_dajam_pca::class => \App\Policies\pencairan_dajam_pca::class,
    \App\Models\pengajuan_dajam::class => \App\Policies\pengajuan_dajamPolicy::class,
    \App\Models\PengajuanDajamTkr::class => \App\Policies\PengajuanDajamTkr::class,
    \App\Models\PpnTkr::class => \App\Policies\PpnTkr::class,
    \App\Models\Rekening::class => \App\Policies\RekeningPolicy::class,
    \App\Models\StokTkr::class => \App\Policies\StokTKR::class,
    \App\Models\verifikasi_dajam_pca::class => \App\Policies\verifikasi_dajam_pca::class,
    \App\Models\verifikasi_dajam::class => \App\Policies\verifikasi_dajamPolicy::class,
    \App\Models\VerifikasiDajamTkr::class => \App\Policies\VerifikasiDajamTkr::class,        
];
    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
