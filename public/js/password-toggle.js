/**
 * Toggle de visibilidad de contraseñas.
 * Uso en el marcado: agregar un disparador con data-password-toggle="#idDelInput"
 * Funciona en cualquier formulario por event delegation (un solo listener).
 */
(function () {
    'use strict';

    document.addEventListener('click', function (event) {
        const trigger = event.target.closest('[data-password-toggle]');
        if (!trigger) return;

        event.preventDefault();

        // 1) Resolver el input objetivo: por selector explícito...
        let input = null;
        const selector = trigger.getAttribute('data-password-toggle');
        if (selector) {
            input = document.querySelector(selector);
        }
        // 2) ...o como fallback, el campo dentro del mismo grupo
        if (!input) {
            const group = trigger.closest('.input-group, .form-group, .mb-3, .password-field');
            if (group) {
                input = group.querySelector('input[type="password"], input[type="text"]');
            }
        }
        if (!input) return;

        const estaVisible = input.getAttribute('type') === 'text';
        input.setAttribute('type', estaVisible ? 'password' : 'text');

        const icon = trigger.querySelector('i, span.fa, span.fas, span.far');
        if (icon) {
            icon.classList.toggle('fa-eye', estaVisible);
            icon.classList.toggle('fa-eye-slash', !estaVisible);
        }

        trigger.setAttribute('aria-label', estaVisible ? 'Mostrar contraseña' : 'Ocultar contraseña');

        // Mantener el foco para poder seguir escribiendo
        input.focus();
        // Cursor al final (evita que el caret salte al inicio)
        const val = input.value;
        input.value = '';
        input.value = val;
    });
})();