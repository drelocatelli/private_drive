<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once(__DIR__ . '/session_verification.php');
require_once(__DIR__  . '/vars.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/5.0.375/pdf_viewer.min.css" integrity="sha512-bt54/qzXTxutlNalAuK/V3dxe1T7ZDqeEYbZPle3G1kOH+K1zKlQE0ZOkdYVwPDxdCFrdLHwneslj7sA5APizQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.4.2/mammoth.browser.min.js"></script>

    <title>Files</title>
</head>
<body>
    <div id="app">
        <div id="content">
            <div id="title">
                <h3>Lista de Arquivos e Pastas</h3>
                <a href="<?= ROOT_DIR . '/logout.php'; ?>">Desconectar</a>
            </div>
            <button v-show="!showingFileList" v-on:click="showingFileList = true" style="margin: 10px 0;">Fechar</button>
            <div id="file_list" v-show="showingFileList">
                <div v-show="isLoading" style="margin-top: 1rem;"> 
                    Obtendo lista de arquivos...
                </div>
                <div id="file-contents"></div>
                <?php
                    // include_once  __DIR__ . '/components/files.php';
                ?>
            </div>
            <div id="loadingFile" style="display: none;">Carregando conteúdo...</div>
        </div>
        <div id="file-content" ref="file_content" v-if="!showingFileList && currentFileUrl">
        </div>
    </div>
</body>
</html>
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script type="module">
    import { getPdf, getDocx, getVideo } from './scripts/get.js';

    const app = Vue.createApp({
        data: () => ({
            showingFileList: true,
            currentFileUrl: null,
            isLoading: true,
            abortController: null
        }),
        mounted() {
            this.loadFiles();
        },
        beforeUnmount() {
            if (this.abortController) {
                this.abortController.abort();
            }
        },
        methods: {
            async loadFiles() {
                if (this.abortController) {
                    this.abortController.abort(); // Cancela requisição anterior, se houver
                }

                this.abortController = new AbortController();
                const { signal } = this.abortController;

                try {
                    const fileContents = document.querySelector('#file-contents');
                    fileContents.innerHTML = '';
                    this.isLoading = true;

                    console.time('fetch files'); // Medir tempo de carregamento
                    const response = await fetch('components/files.php', { signal });
                    console.timeEnd('fetch files');

                    if (!response.ok) throw new Error(`Erro HTTP: ${response.status}`);

                    const data = await response.text();

                    requestAnimationFrame(() => {
                        fileContents.innerHTML = data;
                        this.filterPdfFiles();
                        this.filterDocxFiles();
                        this.filterDocFiles();
                        this.filterVideoFiles();
                    });

                    
                } catch (err) {
                    if (err.name === 'AbortError') {
                        console.warn('Requisição abortada');
                    } else {
                        console.error('Erro ao carregar arquivos', err);
                        alert('Ocorreu um erro ao carregar arquivos');
                    }
                } finally {
                    this.isLoading = false;
                }
            },
            createDownloadBtn(element) {
                const {parentElement} = element;
                const baixarEl = document.createElement('a');
                baixarEl.href = element.href;
                baixarEl.target = '_blank';
                baixarEl.innerHTML = '<button class="baixarEl"><i class="fa-solid fa-download"></i></button>';
                const span = document.createElement('span');
                span.innerHTML = '&nbsp;&nbsp;&nbsp;'
                parentElement.appendChild(span);
                parentElement.appendChild(baixarEl);
            },
            getFile(elements, getCb, cbItems) {
                elements.forEach((element) => {
                    this.createDownloadBtn(element);
                    element.target = '_self';

                    element.onclick = (event) => {
                        event.preventDefault();

                        this.showingFileList = false;

                        const relativePath = event.target.getAttribute('relative-path');
                        this.currentFileUrl = 'request_file.php?file=' + encodeURIComponent(relativePath);
                        
                        this.$nextTick(() => {
                            getCb()
                        });
                    }
                });
            },
            filterDocFiles() {
                const docxs = document.querySelectorAll('a[mimetype="application/msword"]');
                // this.getFile(docxs, async () => await getDocx( this.$refs.file_content, this.currentFileUrl))
            },
            filterDocxFiles() {
                const docxs = document.querySelectorAll('a[mimetype="application/vnd.openxmlformats-officedocument.wordprocessingml.document"]');
                this.getFile(docxs, async () => await getDocx( this.$refs.file_content, this.currentFileUrl))
            },
            filterPdfFiles() {
                const pdfs = document.querySelectorAll('a[mimetype="application/pdf"]');
                this.getFile(pdfs, async () => await getPdf( this.$refs.file_content, this.currentFileUrl));
            },
            filterVideoFiles() {
                const videos = document.querySelectorAll('a[mimetype="video/mp4"]');
                this.getFile(videos, async () => await getVideo( this.$refs.file_content, this.currentFileUrl, 'video/mp4'));
            }
        }
    });

    app.mount('#app');

</script>

<style>
    body {
        padding: 0;
        margin: 0;
    }

    div#file-content canvas {
        width: 90vw;
    }

    .page {
        background: #fff;
        padding: 1rem;

        &#videos {
            background: unset;
            display: flex;
            justify-content: center;
            align-items: center;

            & video {
                width: 70vw;
            }
        }
    }

    #file_list {
        & a {
            color: cadetblue;
            text-transform: capitalize;
            text-decoration: none;

            & button {
                color: black;
                border: none;
                padding: 5px 1rem;
                border-radius: 5px;

                &:hover {
                    background: #ddd;
                    color: #9e9e9e;
                }
            }
            
            &:hover {
                background: yellow;
            }
        }

    }

    #file-content {
        display: none;
        height: -webkit-fill-available;
        position: absolute;
        padding: 1rem;
        width: -webkit-fill-available;
        flex-direction: column;
        align-items: center;
        background: #2A2A2E;
    }

    #fileEmbed {
        height: -webkit-fill-available;
        width: 100vw;
        position: absolute;
    }
    h3 {
        padding: 0;
        margin: 0;
    }
    
    #title {
        display: flex;
        align-items: center;
        gap: 1rem;
        justify-content: space-between;
    }
    
    #content {
        margin: 0 10px;
    }

    ul, li {
        list-style: none;
        user-select: none; 
        cursor: pointer;
        padding:0;
    }
    
    li {
        padding: 0 1rem;

    }
    
    summary, details ul {
        padding-bottom: 8px;

    }

    ul:has(details) {
        width: fit-content;
    }

    #documento {
        background: #f9f9f9;
        padding: 1rem;
        width: 80vw;
        border-radius: 10px;
        height: 100vh;
        overflow-y: auto;
        margin: 10px 0;
    }

    .pageEl {
        height: 100vh;
        overflow-y: auto;
    }

    @media screen and (min-width: 1000px) {
        #file-content canvas {
            width: 50vw!important;
        }

        #documento {
            width: 60vw;
        }

    }
</style>

