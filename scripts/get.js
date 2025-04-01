
async function getPdf(container, url) {
    
    try {
        const loadingEl = document.querySelector('#loadingFile');
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

async function getDocx(container, url) {
    const loadingEl = document.querySelector('#loadingFile');
    loadingEl.style.display = 'block';
    container.style.display = 'none';

    container.innerHTML = '';
    
    try {
        const fileResponse = await fetch(url);
        const fileBlob = await fileResponse.blob();
        
        // Regex para extrair o nome do arquivo (sem a extensão)
        const regex = /(?<=file=%2F[^%]+%2F[^%]+%2F)([^%]+)(?=\.)/;
        const fileName = url.match(regex)[0];

        const formData = new FormData();
        formData.append('docxFile', fileBlob, `${fileName}.docx`);

        // Envia o arquivo para o backend
        const getFile = await fetch('components/office.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });

        // Espera que o backend retorne o HTML
        const htmlContent = await getFile.text();

        // Exibe o HTML na página
        container.innerHTML = htmlContent;
        container.style.display = 'flex';

        loadingEl.style.display = 'none'; // Esconde o loading
    } catch (err) {
        console.error(err);
        loadingEl.style.display = 'none'; // Esconde o loading em caso de erro
    }
}

export { getPdf, getDocx };
