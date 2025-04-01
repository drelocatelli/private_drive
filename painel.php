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
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/5.0.375/pdf_viewer.min.css" integrity="sha512-bt54/qzXTxutlNalAuK/V3dxe1T7ZDqeEYbZPle3G1kOH+K1zKlQE0ZOkdYVwPDxdCFrdLHwneslj7sA5APizQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>

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
                <?php
                    include_once  __DIR__ . '/components/files.php';
                ?>
            </div>
            <div id="loadingFile" style="display: none;">Carregando conteúdo...</div>
        </div>
        <div id="file-content" ref="file_content" v-if="!showingFileList && currentFileUrl">
        </div>
    </div>
</body>
</html>

<script type="module">
    import { getPdf } from './scripts/get_pdf.js';

    const app = Vue.createApp({
        data: () => ({
            showingFileList: true,
            currentFileUrl: null,
        }),
        mounted() {
            this.filterPdfFiles();
        },
        methods: {
            filterPdfFiles() {
                const pdfs = document.querySelectorAll('a[mimetype="application/pdf"]');
                pdfs.forEach((pdf) => {
                    const {parentElement} = pdf;
                    const baixarEl = document.createElement('a');
                    baixarEl.href = pdf.href;
                    baixarEl.target = '_blank';
                    baixarEl.innerHTML = '<button><i class="fa-solid fa-download"></i></button>';

                    const span = document.createElement('span');
                    span.innerHTML = '&nbsp;&nbsp;&nbsp;'
                    parentElement.appendChild(span);
                    parentElement.appendChild(baixarEl);
                    pdf.onclick = (e) => {
                        e.preventDefault();
                        const relativePath = e.target.getAttribute('relative-path');

                        this.showingFileList = false;
                        this.currentFileUrl = 'request_file.php?file=' + encodeURIComponent(relativePath);
                        
                        this.$nextTick(() => {
                            getPdf(this.$refs.file_content, this.currentFileUrl);
                        });

                    }
                });
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
        padding: 1rem;
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
</style>

