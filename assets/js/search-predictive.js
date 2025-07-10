/**
 * Sistema de búsqueda predictiva mejorado con imágenes
 */

class PredictiveSearch {
    constructor(selector = '.search-predictive') {
        this.container = document.querySelector(selector);
        this.input = this.container.querySelector('input[type="text"]');
        this.resultsContainer = null;
        this.searchTimeout = null;
        this.isLoading = false;
        this.currentQuery = '';
        
        this.init();
    }

    init() {
        this.createResultsContainer();
        this.bindEvents();
        this.loadStyles();
    }

    createResultsContainer() {
        this.resultsContainer = document.createElement('div');
        this.resultsContainer.className = 'search-results-container';
        this.container.appendChild(this.resultsContainer);
    }

    bindEvents() {
        // Evento de escritura en el input
        this.input.addEventListener('input', (e) => {
            this.handleInput(e.target.value);
        });

        // Evento de foco
        this.input.addEventListener('focus', () => {
            if (this.currentQuery) {
                this.showResults();
            }
        });

        // Evento de clic fuera para cerrar
        document.addEventListener('click', (e) => {
            if (!this.container.contains(e.target)) {
                this.hideResults();
            }
        });

        // Eventos de teclado
        this.input.addEventListener('keydown', (e) => {
            this.handleKeydown(e);
        });
    }

    handleInput(value) {
        const query = value.trim();
        this.currentQuery = query;

        // Limpiar timeout anterior
        if (this.searchTimeout) {
            clearTimeout(this.searchTimeout);
        }

        if (query.length < 2) {
            this.hideResults();
            return;
        }

        // Mostrar loading
        this.showLoading();

        // Delay para evitar muchas peticiones
        this.searchTimeout = setTimeout(() => {
            this.performSearch(query);
        }, 300);
    }

    async performSearch(query) {
        if (this.isLoading) return;

        this.isLoading = true;
        this.showLoading();

        try {
            const response = await fetch(`search-predictive.php?query=${encodeURIComponent(query)}`);
            const data = await response.json();

            if (data.error) {
                this.showError(data.error);
            } else {
                this.displayResults(data.results, query);
            }
        } catch (error) {
            console.error('Error en búsqueda:', error);
            this.showError('Error al realizar la búsqueda');
        } finally {
            this.isLoading = false;
        }
    }

    displayResults(results, query) {
        if (!results || results.length === 0) {
            this.showNoResults();
            return;
        }

        this.resultsContainer.innerHTML = '';

        results.forEach(result => {
            const resultElement = this.createResultElement(result, query);
            this.resultsContainer.appendChild(resultElement);
        });

        this.showResults();
    }

    createResultElement(result, query) {
        const item = document.createElement('a');
        item.href = result.url;
        item.className = 'search-result-item';
        item.setAttribute('data-type', result.tipo);
        item.setAttribute('data-id', result.id);

        // Agregar parámetros de búsqueda a la URL
        if (query) {
            const url = new URL(result.url, window.location.origin);
            url.searchParams.set('search', query);
            item.href = url.toString();
        }

        let imageHtml = '';
        if (result.imagen) {
            imageHtml = `<img src="${result.imagen}" alt="${result.titulo}" class="search-result-image" onerror="this.src='assets/img/default-course.jpg'">`;
        } else {
            imageHtml = `<div class="search-result-icon" style="background-color: ${result.color}">
                <i class="${result.icon}"></i>
            </div>`;
        }

        let metaHtml = '';
        if (result.precio) {
            metaHtml += `<span class="search-result-price">S/ ${result.precio}</span>`;
        }
        if (result.duracion) {
            metaHtml += `<span class="search-result-duration">${result.duracion}</span>`;
        }
        if (result.categoria) {
            metaHtml += `<span class="search-result-category">${result.categoria}</span>`;
        }

        item.innerHTML = `
            ${imageHtml}
            <div class="search-result-content">
                <div class="search-result-title">${this.highlightText(result.titulo, query)}</div>
                <div class="search-result-description">${this.highlightText(result.descripcion, query)}</div>
                <div class="search-result-meta">
                    ${metaHtml}
                </div>
            </div>
            <div class="search-result-type">${this.getTypeLabel(result.tipo)}</div>
        `;

        // Evento de clic
        item.addEventListener('click', (e) => {
            this.handleResultClick(e, result, query);
        });

        return item;
    }

    highlightText(text, query) {
        if (!query) return text;
        
        const regex = new RegExp(`(${this.escapeRegex(query)})`, 'gi');
        return text.replace(regex, '<span class="search-highlight">$1</span>');
    }

    escapeRegex(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    getTypeLabel(tipo) {
        const labels = {
            'curso': 'Curso',
            'faq': 'FAQ',
            'servicio': 'Servicio',
            'categoria': 'Categoría'
        };
        return labels[tipo] || 'Otro';
    }

    handleResultClick(e, result, query) {
        // Si es una pregunta frecuente, expandir automáticamente
        if (result.tipo === 'faq') {
            // El resaltado se manejará automáticamente por el script de resaltado
            return;
        }

        // Para otros tipos, la navegación normal es suficiente
        // El resaltado se aplicará automáticamente en la página de destino
    }

    handleKeydown(e) {
        const items = this.resultsContainer.querySelectorAll('.search-result-item');
        const currentIndex = Array.from(items).findIndex(item => item.classList.contains('selected'));

        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                this.selectNextItem(items, currentIndex);
                break;
            case 'ArrowUp':
                e.preventDefault();
                this.selectPreviousItem(items, currentIndex);
                break;
            case 'Enter':
                e.preventDefault();
                this.selectCurrentItem(items, currentIndex);
                break;
            case 'Escape':
                this.hideResults();
                this.input.blur();
                break;
        }
    }

    selectNextItem(items, currentIndex) {
        const nextIndex = currentIndex < items.length - 1 ? currentIndex + 1 : 0;
        this.selectItem(items, nextIndex);
    }

    selectPreviousItem(items, currentIndex) {
        const prevIndex = currentIndex > 0 ? currentIndex - 1 : items.length - 1;
        this.selectItem(items, prevIndex);
    }

    selectItem(items, index) {
        items.forEach(item => item.classList.remove('selected'));
        if (items[index]) {
            items[index].classList.add('selected');
            items[index].scrollIntoView({ block: 'nearest' });
        }
    }

    selectCurrentItem(items, currentIndex) {
        if (currentIndex >= 0 && items[currentIndex]) {
            items[currentIndex].click();
        }
    }

    showResults() {
        this.resultsContainer.classList.add('show');
    }

    hideResults() {
        this.resultsContainer.classList.remove('show');
    }

    showLoading() {
        this.resultsContainer.innerHTML = '<div class="search-loading">Buscando...</div>';
        this.showResults();
    }

    showNoResults() {
        this.resultsContainer.innerHTML = '<div class="search-no-results">No se encontraron resultados</div>';
        this.showResults();
    }

    showError(message) {
        this.resultsContainer.innerHTML = `<div class="search-no-results">${message}</div>`;
        this.showResults();
    }

    loadStyles() {
        if (!document.getElementById('search-predictive-styles')) {
            const link = document.createElement('link');
            link.id = 'search-predictive-styles';
            link.rel = 'stylesheet';
            link.href = 'assets/css/search-results.css';
            document.head.appendChild(link);
        }
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    const searchContainer = document.querySelector('.search-predictive');
    if (searchContainer) {
        window.predictiveSearch = new PredictiveSearch('.search-predictive');
    }
});

// Función global para uso desde otros scripts
function initPredictiveSearch(selector) {
    return new PredictiveSearch(selector);
} 