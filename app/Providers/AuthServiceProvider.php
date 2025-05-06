<?php

namespace App\Providers;
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
