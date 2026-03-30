document.addEventListener("DOMContentLoaded", function () {

    /* ==========================
       ⭐ SISTEMA DE ESTRELAS
    ========================== */
document.querySelectorAll(".star-rating").forEach(rating => {

    const stars = rating.querySelectorAll(".star");
    let notaAtual = parseFloat(rating.dataset.nota) || 0;

    atualizarEstrelas(stars, notaAtual);

    stars.forEach(star => {

        star.addEventListener("mousemove", function(e) {

            const rect = this.getBoundingClientRect();
            const metade = e.clientX - rect.left < rect.width / 2;

            let valorBase = parseInt(this.dataset.value);
            let valor = metade ? valorBase - 0.5 : valorBase;

            atualizarEstrelas(stars, valor);
        });

        star.addEventListener("mouseleave", function() {
            atualizarEstrelas(stars, notaAtual);
        });

        star.addEventListener("click", function(e) {

            const rect = this.getBoundingClientRect();
            const metade = e.clientX - rect.left < rect.width / 2;

            let valorBase = parseInt(this.dataset.value);
            let valor = metade ? valorBase - 0.5 : valorBase;

            notaAtual = valor;
            rating.dataset.nota = valor;

            atualizarEstrelas(stars, valor);

            let form = rating.closest("form");
            let formData = new FormData(form);
            formData.append("nota", valor);
            formData.append("csrf_token", CSRF_TOKEN);


fetch(BASE_URL + "actions/salvar_avaliacao.php", { method: "POST", body: formData })
    .then(() => {
        let row = rating.closest(".track-row");
        let heart = row.querySelector(".heart-btn");

        if (heart) heart.classList.remove("disabled");

        atualizarBarraProgresso();        
    });


        });

    });

});

function atualizarEstrelas(stars, nota) {

    stars.forEach(star => {

        const valor = parseInt(star.dataset.value);

        star.classList.remove("filled", "half");

        if (valor <= Math.floor(nota)) {
            star.classList.add("filled");
        }
        else if (Math.abs((valor - 0.5) - nota) < 0.01) {
            star.classList.add("half");
        }
    });
}


    /* ==========================
       ♥ FAVORITO
    ========================== */

document.querySelectorAll(".heart-btn").forEach(btn => {

    btn.addEventListener("click", function () {

        if (this.classList.contains("disabled")) return;

        this.classList.toggle("active");

        let favorita = this.classList.contains("active") ? 1 : 0;
        let faixaId = this.dataset.faixaId;

        let formData = new FormData();
        formData.append("faixa_id", faixaId);
        formData.append("favorita", favorita);
        formData.append("csrf_token", CSRF_TOKEN);

        fetch(BASE_URL + "actions/salvar_avaliacao.php", {
            method: "POST",
            body: formData
        });
    });

});




    /* ==========================
       ▶ +1 REPRODUÇÃO
    ========================== */

    document.querySelectorAll(".play-btn").forEach(btn => {

        btn.addEventListener("click", function () {

            let row = this.closest(".track-row");
            let countSpan = row.querySelector(".play-count");
            let faixaId = row.querySelector("input[name='faixa_id']").value;

            let count = parseInt(countSpan.textContent);
            count++;
            countSpan.textContent = count;

            let formData = new FormData();
            formData.append("faixa_id", faixaId);
            formData.append("csrf_token", CSRF_TOKEN);


            fetch(BASE_URL + "actions/registrar_reproducao.php", {
                method: "POST",
                body: formData
            })
            .then(() => atualizarBarraProgresso());
        });
    });

    /* ==========================
       − REMOVER REPRODUÇÃO 
    ========================== */

    document.querySelectorAll(".back-btn").forEach(btn => {

        btn.addEventListener("click", function () {

            let row = this.closest(".track-row");
            let countSpan = row.querySelector(".play-count");
            let faixaId = row.querySelector("input[name='faixa_id']").value;

            let count = parseInt(countSpan.textContent);

            if (count > 0) {
                count--;
                countSpan.textContent = count;

                let formData = new FormData();
                formData.append("faixa_id", faixaId);
                formData.append("csrf_token", CSRF_TOKEN);


                fetch(BASE_URL + "actions/remover_reproducao.php", {
                    method: "POST",
                    body: formData
                })
                .then(() => atualizarBarraProgresso());
            }
        });
    });
    
function atualizarBarraProgresso() {

    let rows = document.querySelectorAll(".track-row");
    let totalFaixas = rows.length;
    let faixasConcluidas = 0;

    rows.forEach(row => {

        let count = parseInt(row.querySelector(".play-count").textContent);
        let nota = parseFloat(row.querySelector(".star-rating").dataset.nota) || 0;

        let contrib = 0;

        if (count > 0) contrib += 0.5;
        if (nota > 0) contrib += 0.5;

        faixasConcluidas += contrib;

    });

    let progressoPercent = totalFaixas > 0
        ? Math.round((faixasConcluidas / totalFaixas) * 100)
        : 0;

    let fill = document.querySelector(".album-progress-fill");
    let text = document.querySelector(".album-progress-text");

    if (fill) fill.style.width = progressoPercent + "%";
    if (text) text.textContent = progressoPercent + "%";


    // =========================
    // SALVAR NO BANCO
    // =========================

    let albumId = document
        .querySelector(".album-container")
        .dataset.albumId;

    let formData = new FormData();
    formData.append("album_id", albumId);
    formData.append("progresso", progressoPercent);
    formData.append("csrf_token", CSRF_TOKEN);


    fetch(BASE_URL + "actions/salvar_progresso.php", {
        method: "POST",
        body: formData
    });

}




});
