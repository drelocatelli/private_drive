async function getPdf(container, url) {
    const loadingEl = document.querySelector('#loadingFile');
    
    try {
        loadingEl.style.display = 'block';
        // Obtém o documento PDF
        const pdf = await pdfjsLib.getDocument(url).promise;

        // Seleciona ou cria um contêiner para exibir as páginas
        container.innerHTML = '';
        container.style.display = 'flex';
        loadingEl.style.display = 'none';

        // Renderiza todas as páginas
        for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
            const page = await pdf.getPage(pageNum);

            // Cria um canvas para a página atual
            const canvas = document.createElement('canvas');
            container.appendChild(document.createElement('br'));
            container.appendChild(canvas);
            const context = canvas.getContext('2d');

            // Define as dimensões do viewport
            const viewport = page.getViewport({ scale: 1.5 });
            canvas.width = viewport.width;
            canvas.height = viewport.height;

            // Renderiza a página no canvas
            await page.render({
                canvasContext: context,
                viewport: viewport
            }).promise;
        }
        

    } catch (err) {
        loadingEl.innerHTML = 'Ocorreu um erro inesperado';
        console.error('Erro ao carregar o PDF:', err);
    } finally {
        setTimeout(() => {
            loadingEl.style.display = 'none';
        }, 1000);
    }
}

// Exportando a função (caso seja usada como módulo ES6)
export { getPdf };
