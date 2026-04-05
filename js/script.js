$(window).on('load',function(){
    $('.burger-menu').click(function(){
       $('.main-menu').slideToggle(100);
    });

    $('.arrow-sub').click(function(){
        var el = $(this);

        el.parent().find('.submenu').slideToggle(100);
    })

    $('.filter-buttons-toggle').click(function(){
       $('.filters-form').slideToggle();
    });

    $('.filters-form').on('change', function(){
        $('.filters-form').submit();
    });

    $('.loader svg').addClass('active');
    setTimeout(function(){
        $('.loader').fadeOut();
    },1200);

});



$('.popin-contact .close').click(function(){
    $('.popin-contact').slideUp();
});

$('a').click(function(event){
   var el = $(this);

   if(el.attr('href') == "#devis"){
       event.preventDefault();
       $('.popin-contact').slideDown();
   }
});


$('.beforeAfter').beforeAfter({
    movable: true,
    clickMove: true,
    position: 50,
    separatorColor: '#fafafa',
    bulletColor: '#fafafa',
    onMoveStart: function(e) {
        console.log(event.target);
    },
    onMoving: function() {
        console.log(event.target);
    },
    onMoveEnd: function() {
        console.log(event.target);
    },
});

jQuery(function ($) {
    var $form = $('#impactexpo-lead-form');
    if (!$form.length) {
        return;
    }

    var ajaxUrl =
        $form.data('ajax-url') ||
        (typeof impactexpoLead !== 'undefined' && impactexpoLead.ajaxUrl
            ? impactexpoLead.ajaxUrl
            : '');

    var $projectBox = $('#impactexpo-project-fields');
    var $feedback = $('#impactexpo-lead-feedback');
    var $leadBody = $('#impactexpo-lead-body');
    var $fileList = $('#impactexpo-file-list');
    var maxFiles = 3;
    var $sourceUrl = $('#impactexpo-source-url');

    function impactexpoSetSourceUrl() {
        if (!$sourceUrl.length) {
            return;
        }
        try {
            $sourceUrl.val(String(window.location.href).split('#')[0]);
        } catch (e1) {}
    }

    impactexpoSetSourceUrl();

    function impactexpoNormalizePhoneFr(phone) {
        var p = String(phone).replace(/[\s().-]/g, '');
        if (!p) {
            return '';
        }
        if (/^\+[1-9]\d{6,14}$/.test(p)) {
            return p;
        }
        var m0 = p.match(/^0([1-9])(\d{8})$/);
        if (m0) {
            return '+33' + m0[1] + m0[2];
        }
        if (/^33([1-9]\d{8})$/.test(p)) {
            return '+' + p;
        }
        return p;
    }

    function impactexpoPhoneValid(phone) {
        return /^\+[1-9]\d{6,14}$/.test(impactexpoNormalizePhoneFr(phone));
    }

    function impactexpoHasLetter(str) {
        try {
            return /\p{L}/u.test(str);
        } catch (err) {
            return /[a-zA-ZÀ-ÿ]/.test(str);
        }
    }

    var impactexpoPlaceholders = [
        'test',
        'tests',
        'xxx',
        'xxxx',
        'aaa',
        'aaaa',
        'abc',
        'lorem',
        'ipsum',
        'n/a',
        'na',
        'none',
        'rien'
    ];

    function impactexpoIsPlaceholder(s) {
        return impactexpoPlaceholders.indexOf(s.trim().toLowerCase()) !== -1;
    }

    function impactexpoEmailValid(s) {
        s = s.trim();
        if (s.length < 6 || s.length > 100) {
            return false;
        }
        return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/i.test(s);
    }

    function impactexpoZipFrValid(zip) {
        return /^\d{5}$/.test((zip || '').trim());
    }

    /**
     * Mêmes règles que impactexpo_lead_validate_field_formats() côté PHP.
     */
    function impactexpoValidateLeadFormats($form) {
        var val = function (name) {
            return ($form.find('[name="' + name + '"]').val() || '').trim();
        };
        var focus = function (name) {
            var el = $form.find('[name="' + name + '"]')[0];
            if (el) {
                el.focus();
            }
        };

        var company = val('company_name');
        if (company.length < 2 || company.length > 120 || !impactexpoHasLetter(company)) {
            focus('company_name');
            return {
                ok: false,
                message:
                    'Le nom de la société doit contenir entre 2 et 120 caractères et au moins une lettre.'
            };
        }
        if (impactexpoIsPlaceholder(company)) {
            focus('company_name');
            return { ok: false, message: 'Merci d’indiquer le véritable nom de votre société.' };
        }

        var addr = val('company_address');
        if (addr.length < 5 || addr.length > 250 || !impactexpoHasLetter(addr)) {
            focus('company_address');
            return {
                ok: false,
                message:
                    'L’adresse de la société doit contenir entre 5 et 250 caractères et au moins une lettre.'
            };
        }

        var zip = val('company_zip');
        if (!impactexpoZipFrValid(zip)) {
            focus('company_zip');
            return {
                ok: false,
                message: 'Le code postal de la société doit comporter exactement 5 chiffres.'
            };
        }

        var city = val('company_city');
        if (city.length < 2 || city.length > 100 || !impactexpoHasLetter(city)) {
            focus('company_city');
            return {
                ok: false,
                message: 'La ville doit contenir entre 2 et 100 caractères et au moins une lettre.'
            };
        }
        if (impactexpoIsPlaceholder(city)) {
            focus('company_city');
            return { ok: false, message: 'Merci d’indiquer le nom réel de la ville.' };
        }

        var fn = val('first_name');
        if (fn.length < 2 || fn.length > 80 || !impactexpoHasLetter(fn)) {
            focus('first_name');
            return { ok: false, message: 'Votre prénom doit contenir entre 2 et 80 caractères et au moins une lettre.' };
        }
        if (impactexpoIsPlaceholder(fn)) {
            focus('first_name');
            return { ok: false, message: 'Merci d’indiquer votre véritable prénom.' };
        }

        var ln = val('last_name');
        if (ln.length < 2 || ln.length > 80 || !impactexpoHasLetter(ln)) {
            focus('last_name');
            return { ok: false, message: 'Votre nom doit contenir entre 2 et 80 caractères et au moins une lettre.' };
        }
        if (impactexpoIsPlaceholder(ln)) {
            focus('last_name');
            return { ok: false, message: 'Merci d’indiquer votre véritable nom.' };
        }

        var em = val('email');
        if (!impactexpoEmailValid(em)) {
            focus('email');
            return { ok: false, message: 'Veuillez indiquer une adresse e-mail valide.' };
        }

        var mob = val('mobile');
        if (!impactexpoPhoneValid(mob)) {
            focus('mobile');
            return {
                ok: false,
                message:
                    'Le numéro de portable n’est pas valide. Utilisez un numéro français (ex. 06 12 34 56 78) ou le format international (+33…).'
            };
        }

        var pzip = val('project_zip');
        var paddr = val('project_address');
        var pcity = val('project_city');
        if (pzip !== '' && !impactexpoZipFrValid(pzip)) {
            focus('project_zip');
            return {
                ok: false,
                message: 'Le code postal du projet doit comporter exactement 5 chiffres.'
            };
        }
        if (paddr !== '' || pcity !== '') {
            if (!impactexpoZipFrValid(pzip)) {
                focus('project_zip');
                return {
                    ok: false,
                    message:
                        'Renseignez un code postal du projet valide (5 chiffres) lorsque l’adresse ou la ville du projet est indiquée.'
                };
            }
            if (paddr !== '') {
                if (paddr.length < 3 || paddr.length > 250 || !impactexpoHasLetter(paddr)) {
                    focus('project_address');
                    return {
                        ok: false,
                        message: 'L’adresse du projet doit être complète (au moins 3 caractères et une lettre).'
                    };
                }
            }
            if (pcity !== '') {
                if (pcity.length < 2 || !impactexpoHasLetter(pcity)) {
                    focus('project_city');
                    return { ok: false, message: 'La ville du projet doit contenir au moins 2 lettres.' };
                }
            }
        }

        var desc = val('description');
        if (desc !== '') {
            if (desc.length < 3 || desc.length > 10000) {
                focus('description');
                return {
                    ok: false,
                    message: 'La description doit faire au moins 3 caractères si vous la renseignez.'
                };
            }
            if (impactexpoIsPlaceholder(desc)) {
                focus('description');
                return { ok: false, message: 'Merci de décrire votre projet de façon plus précise.' };
            }
        }

        return { ok: true };
    }

    function impactexpoFormatFileSize(bytes) {
        if (bytes < 1024) {
            return bytes + ' o';
        }
        if (bytes < 1048576) {
            return (bytes / 1024).toFixed(1) + ' Ko';
        }
        return (bytes / 1048576).toFixed(1) + ' Mo';
    }

    $(document).on('click', '.impactexpo-rgpd a', function (e) {
        e.stopPropagation();
    });

    $(document).on('change', '#impactexpo-project-diff', function () {
        if ($(this).is(':checked')) {
            $projectBox.prop('hidden', false).removeClass('is-hidden');
        } else {
            $projectBox.prop('hidden', true).addClass('is-hidden');
        }
    });

    $(document).on('change', '#impactexpo-lead-files', function () {
        var files = this.files;
        $fileList.empty();
        if (!files || !files.length) {
            $fileList.prop('hidden', true);
            return;
        }
        var n = Math.min(files.length, maxFiles);
        for (var i = 0; i < n; i++) {
            var name = files[i].name;
            var size = impactexpoFormatFileSize(files[i].size);
            $fileList.append($('<li></li>').text(name + ' (' + size + ')'));
        }
        if (files.length > maxFiles) {
            $fileList.append(
                $('<li class="impactexpo-file-list-warn"></li>').text(
                    'Seules les ' + maxFiles + ' premières pièces jointes seront envoyées.'
                )
            );
        }
        $fileList.prop('hidden', false);
    });

    $form.on('submit', function (e) {
        e.preventDefault();

        var formEl = this;
        $feedback.prop('hidden', true).removeClass('is-success is-error').text('');

        var $rgpd = $('#impactexpo-rgpd');
        if ($rgpd.length && !$rgpd.is(':checked')) {
            $feedback
                .addClass('is-error')
                .text(
                    'Veuillez accepter la politique de confidentialité pour envoyer le formulaire.'
                )
                .prop('hidden', false);
            try {
                $rgpd[0].focus();
            } catch (err) {}
            return;
        }

        if (!formEl.checkValidity()) {
            formEl.reportValidity();
            return;
        }

        var fmt = impactexpoValidateLeadFormats($form);
        if (!fmt.ok) {
            $feedback.addClass('is-error').text(fmt.message).prop('hidden', false);
            return;
        }

        if (!ajaxUrl) {
            $feedback
                .addClass('is-error')
                .text(
                    'Erreur de configuration : URL d’envoi indisponible. Contactez le support technique.'
                )
                .prop('hidden', false);
            return;
        }

        impactexpoSetSourceUrl();

        var $submit = $form.find('.impactexpo-lead-submit');
        var fd = new FormData(formEl);
        fd.append('action', 'impactexpo_submit_lead');

        $submit.prop('disabled', true);

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false
        })
            .done(function (res) {
                if (res && res.success && res.data && res.data.message) {
                    $form[0].reset();
                    $projectBox.prop('hidden', true).addClass('is-hidden');
                    $('#impactexpo-project-diff').prop('checked', false);
                    $fileList.empty().prop('hidden', true);
                    $form.addClass('impactexpo-lead-form--sent');
                    if ($leadBody.length) {
                        $leadBody.prop('hidden', true);
                    }
                    $feedback
                        .removeClass('is-error')
                        .addClass('is-success')
                        .text(res.data.message)
                        .prop('hidden', false);
                    var elFb = $feedback[0];
                    if (elFb && typeof elFb.scrollIntoView === 'function') {
                        window.requestAnimationFrame(function () {
                            elFb.scrollIntoView({
                                behavior: 'smooth',
                                block: 'start',
                                inline: 'nearest'
                            });
                        });
                    }
                } else {
                    var msg =
                        res && res.data && res.data.message
                            ? res.data.message
                            : 'Une erreur est survenue.';
                    $feedback.addClass('is-error').text(msg).prop('hidden', false);
                }
            })
            .fail(function (xhr) {
                var msg = 'Une erreur est survenue. Veuillez réessayer.';
                if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                    msg = xhr.responseJSON.data.message;
                }
                $feedback.addClass('is-error').text(msg).prop('hidden', false);
            })
            .always(function () {
                $submit.prop('disabled', false);
            });
    });
});