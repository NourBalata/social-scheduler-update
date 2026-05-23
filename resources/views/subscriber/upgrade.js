(function () {
    function openUpgradeModal(reason) {
        const modal    = document.getElementById('upgradeModal');
        const title    = document.getElementById('upModal-title');
        const subtitle = document.getElementById('upModal-subtitle');
        const banner   = document.getElementById('upModal-banner');
 
        const configs = {
            expired: {
                title:    'Your Plan Has Expired',
                subtitle: 'Renew now to continue scheduling posts',
                banner:   '🔒 Your access has ended. Pick a plan below to get back up and running.',
                bannerBg: '#fee2e2', bannerColor: '#991b1b',
            },
            limit: {
                title:    "You've Hit Your Post Limit",
                subtitle: 'Upgrade to schedule more posts this month',
                banner:   "📊 You've used all your posts for this month. Upgrade for more.",
                bannerBg: '#fff3cd', bannerColor: '#856404',
            },
            free_limit: {
                title:    'Unlock Full Power',
                subtitle: 'You need a paid plan to use this feature',
                banner:   '✨ This feature is available on paid plans.',
                bannerBg: '#eff6ff', bannerColor: '#1d4ed8',
            },
            manual: {
                title:    'Upgrade Your Plan',
                subtitle: 'Unlock full access to all features',
                banner:   '', bannerBg: '', bannerColor: '',
            },
        };
 
        const cfg = configs[reason] || configs.manual;
        title.textContent    = cfg.title;
        subtitle.textContent = cfg.subtitle;
 
        if (cfg.banner) {
            banner.textContent      = cfg.banner;
            banner.style.background = cfg.bannerBg;
            banner.style.color      = cfg.bannerColor;
            banner.style.display    = 'flex';
        } else {
            banner.style.display = 'none';
        }
 
        modal.style.display          = 'flex';
        document.body.style.overflow = 'hidden';
    }
 
    function closeUpgradeModal() {
        document.getElementById('upgradeModal').style.display = 'none';
        document.body.style.overflow = '';
    }
 
    window.openUpgradeModal  = openUpgradeModal;
    window.closeUpgradeModal = closeUpgradeModal;
 

    document.getElementById('upgradeModal').addEventListener('click', function (e) {
        if (e.target === this) closeUpgradeModal();
    });
 

    if($autoShowUpgrade)
        document.addEventListener('DOMContentLoaded', function () {
            openUpgradeModal('{{ $autoUpgradeReason }}');
        });
    endif
})();