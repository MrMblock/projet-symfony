import { Controller } from '@hotwired/stimulus';

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="copy-code" attribute will cause
 * this controller to be executed. The name "copy-code" comes from the filename:
 * copy_code_controller.js -> "copy-code"
 *
 * Delete this file or adapt it for your use!
 */
export default class extends Controller {
    connect() {
        this.element.querySelectorAll('pre').forEach((pre) => {
            // Check if already processed to avoid duplicates
            if (pre.parentNode.classList.contains('code-wrapper')) {
                return;
            }

            // Create wrapper
            const wrapper = document.createElement('div');
            wrapper.classList.add('code-wrapper');
            wrapper.style.position = 'relative';

            // Insert wrapper before pre
            pre.parentNode.insertBefore(wrapper, pre);

            // Move pre into wrapper
            wrapper.appendChild(pre);

            // Create copy button
            const button = document.createElement('button');
            button.className = 'btn-copy-code';
            button.textContent = 'Copier';
            button.type = 'button';

            // Add click event
            button.addEventListener('click', () => {
                const code = pre.querySelector('code') ? pre.querySelector('code').innerText : pre.innerText;

                navigator.clipboard.writeText(code).then(() => {
                    const originalText = button.textContent;
                    button.textContent = 'Copié !';
                    button.classList.add('copied');

                    setTimeout(() => {
                        button.textContent = originalText;
                        button.classList.remove('copied');
                    }, 2000);
                }).catch(err => {
                    console.error('Failed to copy text: ', err);
                });
            });

            wrapper.appendChild(button);
        });
    }
}
