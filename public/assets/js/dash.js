document.addEventListener("DOMContentLoaded", function () {

    document.querySelectorAll(".favorite-remove").forEach(btn => {

        btn.addEventListener("click", function () {

            const faixaId = this.dataset.faixaId;
            const row = this.closest(".favorite-row");

            if (!faixaId || !row) {
                return;
            }

            // ==========================================
            // SEGUNDO CLIQUE
            // DESFAZ A REMOÇÃO
            // ==========================================

            if (btn.dataset.removendo === "1") {

                clearTimeout(btn._removeTimer);

                const formData = new FormData();

                formData.append("faixa_id", faixaId);
                formData.append("favorita", "1");
                formData.append("csrf_token", CSRF_TOKEN);

                fetch(BASE_URL + "actions/salvar_avaliacao.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => {

                    if (!response.ok) {
                        throw new Error("Erro HTTP " + response.status);
                    }

                    return response.json();

                })
                .then(() => {

                    btn.dataset.removendo = "0";

                    row.classList.remove("removing");

                    btn.classList.add("active");

                    btn.title = "Remover dos favoritos";

                })
                .catch(error => {

                    console.error(error);

                    alert("Não foi possível restaurar a música aos favoritos.");

                });

                return;
            }


            // ==========================================
            // PRIMEIRO CLIQUE
            // REMOVE DO BANCO
            // ==========================================

            const formData = new FormData();

            formData.append("faixa_id", faixaId);
            formData.append("favorita", "0");
            formData.append("csrf_token", CSRF_TOKEN);

            fetch(BASE_URL + "actions/salvar_avaliacao.php", {
                method: "POST",
                body: formData
            })
            .then(response => {

                if (!response.ok) {
                    throw new Error("Erro HTTP " + response.status);
                }

                return response.json();

            })
            .then(() => {

                // Marca como "aguardando remoção visual"
                btn.dataset.removendo = "1";

                // Deixa o coração cinza
                btn.classList.remove("active");

                btn.title = "Clique novamente para desfazer";

                // Efeito visual
                row.classList.add("removing");


                // ==========================================
                // AGUARDA 10 SEGUNDOS
                // ==========================================

                btn._removeTimer = setTimeout(() => {

                    row.remove();

                    const lista = document.querySelector(".favorite-list");

                    if (lista && lista.children.length === 0) {

                        const mensagem = document.createElement("p");

                        mensagem.textContent =
                            "Você ainda não marcou nenhuma música como favorita.";

                        lista.replaceWith(mensagem);
                    }

                }, 10000);

            })
            .catch(error => {

                console.error(error);

                alert("Não foi possível remover a música dos favoritos.");

            });

        });

    });

});