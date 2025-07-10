/**
 * Sistema de resaltado y parpadeo para búsquedas
 */

class SearchHighlighter {
    constructor() {
        this.highlightClass = 'search-highlight';
        this.blinkClass = 'search-blink';
        this.query = '';
        this.init();
    }

    init() {
        // Verificar si hay parámetros de búsqueda en la URL
        this.checkUrlParams();
        
        // Aplicar estilos CSS
        this.addStyles();
    }

    checkUrlParams() {
        const urlParams = new URLSearchParams(window.location.search);
        const searchQuery = urlParams.get('search');
        const highlightId = urlParams.get('highlight');
        
        if (searchQuery) {
            this.query = decodeURIComponent(searchQuery);
            this.highlightText();
            
            // Si hay un ID específico para resaltar, hacer scroll hacia él
            if (highlightId) {
                setTimeout(() => {
                    this.scrollToElement(highlightId);
                }, 500);
            }
        }
    }

    highlightText() {
        if (!this.query || this.query.length < 2) return;

        // Buscar en todo el contenido de la página
        this.highlightInElement(document.body);
        
        // Aplicar efecto de parpadeo
        this.applyBlinkEffect();
    }

    highlightInElement(element) {
        const walker = document.createTreeWalker(
            element,
            NodeFilter.SHOW_TEXT,
            {
                acceptNode: function(node) {
                    // Excluir scripts, estilos y elementos de búsqueda
                    const parent = node.parentElement;
                    if (parent && (
                        parent.tagName === 'SCRIPT' || 
                        parent.tagName === 'STYLE' || 
                        parent.classList.contains('search-results') ||
                        parent.classList.contains('search-input')
                    )) {
                        return NodeFilter.FILTER_REJECT;
                    }
                    return NodeFilter.FILTER_ACCEPT;
                }
            }
        );

        const textNodes = [];
        let node;
        while (node = walker.nextNode()) {
            textNodes.push(node);
        }

        textNodes.forEach(textNode => {
            const text = textNode.textContent;
            const regex = new RegExp(`(${this.escapeRegex(this.query)})`, 'gi');
            
            if (regex.test(text)) {
                const highlightedText = text.replace(regex, '<span class="' + this.highlightClass + '">$1</span>');
                const wrapper = document.createElement('span');
                wrapper.innerHTML = highlightedText;
                textNode.parentNode.replaceChild(wrapper, textNode);
            }
        });
    }

    applyBlinkEffect() {
        const highlights = document.querySelectorAll('.' + this.highlightClass);
        
        highlights.forEach((highlight, index) => {
            // Aplicar parpadeo con delay escalonado
            setTimeout(() => {
                highlight.classList.add(this.blinkClass);
                
                // Remover la clase de parpadeo después de 3 segundos
                setTimeout(() => {
                    highlight.classList.remove(this.blinkClass);
                }, 3000);
            }, index * 100);
        });
    }

    scrollToElement(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
            
            // Resaltar específicamente este elemento
            this.highlightElement(element);
        }
    }

    highlightElement(element) {
        // Agregar borde temporal al elemento
        element.style.border = '3px solid #ffc107';
        element.style.borderRadius = '8px';
        element.style.boxShadow = '0 0 20px rgba(255, 193, 7, 0.5)';
        element.style.transition = 'all 0.3s ease';
        
        // Remover el resaltado después de 5 segundos
        setTimeout(() => {
            element.style.border = '';
            element.style.borderRadius = '';
            element.style.boxShadow = '';
        }, 5000);
    }

    escapeRegex(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    addStyles() {
        if (document.getElementById('search-highlight-styles')) return;

        const style = document.createElement('style');
        style.id = 'search-highlight-styles';
        style.textContent = `
            .search-highlight {
                background-color: #fff3cd;
                color: #856404;
                padding: 2px 4px;
                border-radius: 3px;
                font-weight: bold;
                transition: all 0.3s ease;
            }
            
            .search-blink {
                animation: searchBlink 1s ease-in-out infinite;
            }
            
            @keyframes searchBlink {
                0%, 100% {
                    background-color: #fff3cd;
                    color: #856404;
                    transform: scale(1);
                }
                50% {
                    background-color: #ffc107;
                    color: #000;
                    transform: scale(1.05);
                    box-shadow: 0 0 10px rgba(255, 193, 7, 0.5);
                }
            }
            
            .search-highlight:hover {
                background-color: #ffeaa7;
                transform: scale(1.02);
            }
        `;
        document.head.appendChild(style);
    }

    // Método para resaltar texto específico (para uso manual)
    highlightSpecificText(text, elementId = null) {
        this.query = text;
        if (elementId) {
            const element = document.getElementById(elementId);
            if (element) {
                this.highlightInElement(element);
            }
        } else {
            this.highlightText();
        }
    }

    // Método para limpiar todos los resaltados
    clearHighlights() {
        const highlights = document.querySelectorAll('.' + this.highlightClass);
        highlights.forEach(highlight => {
            const parent = highlight.parentNode;
            parent.replaceChild(document.createTextNode(highlight.textContent), highlight);
            parent.normalize();
        });
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    window.searchHighlighter = new SearchHighlighter();
});

// Función global para uso desde otros scripts
function highlightSearchText(query, elementId = null) {
    if (window.searchHighlighter) {
        window.searchHighlighter.highlightSpecificText(query, elementId);
    }
}

function clearSearchHighlights() {
    if (window.searchHighlighter) {
        window.searchHighlighter.clearHighlights();
    }
} 