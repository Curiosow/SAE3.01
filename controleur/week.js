document.addEventListener("DOMContentLoaded", function () {
    loadOffset(-1);
    loadOffset(1);
    loadWeekRange();
    loadWeekDays();
    loadDaysAjax();

    // Ajout d'event listeners aux boutons pour mettre à jour l'offset et appeler la fonction updateSession avec le nouvel offset
    document.getElementById("buttonback").addEventListener("click", function (e) {
        const hiddenInput = document.getElementById("weekOffsetValue");
        let currentOffset = parseInt(hiddenInput.value, 10);
        let newOffset = currentOffset - 1;
        hiddenInput.value = newOffset;
        updateSessionOffset(newOffset);
    });

    document.getElementById("buttonnext").addEventListener("click", function (e) {
        const hiddenInput = document.getElementById("weekOffsetValue");
        let currentOffset = parseInt(hiddenInput.value, 10);
        let newOffset = currentOffset + 1;
        hiddenInput.value = newOffset;
        updateSessionOffset(newOffset);
    });

    // Fonction pour mettre à jour l'offset de la session en Ajax
    function updateSessionOffset(newOffset) {
        const formData = new FormData();
        formData.append("weekOffSet", newOffset);
        fetch("Dashboard.php", {
            method: "POST",
            body: formData
        })
            .then(response => response.text())
            .then(data => {
                console.log("Session mise à jour. Nouvel offset:", newOffset);
                loadOffset(-1);
                loadOffset(1);
                loadWeekRange();
                loadWeekDays();
                loadDaysAjax();
            })
            .catch(error => console.error("Erreur dans la mise à jour de l'offset", error));
    }

    function loadOffset(offset) {
        const xhr = new XMLHttpRequest();

        xhr.onreadystatechange = function () {
            if (this.readyState === 4) {
                if (this.status === 200) {
                    const data = JSON.parse(this.responseText);
                    console.log("Offset:", data);

                    let button;
                    if (offset === -1) {
                        button = document.getElementById("buttonback");
                    } else if (offset === 1) {
                        button = document.getElementById("buttonnext");
                    }

                    if (button) {
                        button.setAttribute("value", data);
                    } else {
                        console.error("Bouton non trouvé pour l'offset:", offset);
                    }
                } else {
                    console.error("Erreur de statut:", this.status);
                }
            }
        };

        xhr.open("GET", "/controleur/Offset.php?offset=" + offset, true);
        xhr.send();
        console.log("Requête Ajax envoyé avec offset:", offset);
    }

    function loadWeekRange() {
        const xhr = new XMLHttpRequest();

        xhr.onreadystatechange = function () {
            if (this.readyState === 4) {
                if (this.status === 200) {
                    console.log("Réponse de la fonction range :", this.responseText);
                    try {
                        const data = JSON.parse(this.responseText);
                        const weekRangeElement = document.getElementById("weekRange");
                        weekRangeElement.textContent = `Du ${data.start} au ${data.end}`;
                    } catch (e) {
                        console.error("Erreur dans le parsing JSON:", e);
                    }
                } else {
                    console.error("Erreur de statut:", this.status);
                }
            }
        };

        xhr.open("GET", "/controleur/getWeekRange.php", true);
        xhr.send();
    }

    function loadWeekDays() {
        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (this.readyState === 4) {
                if (this.status === 200) {
                    try {
                        let data = JSON.parse(this.responseText);

                        document.getElementById('day-lundi').setAttribute("data-date", data.lundi.date);
                        document.getElementById("day-lundi-span").textContent = data.lundi.display;

                        document.getElementById('day-mardi').setAttribute("data-date", data.mardi.date);
                        document.getElementById("day-mardi-span").textContent = data.mardi.display;

                        document.getElementById('day-mercredi').setAttribute("data-date", data.mercredi.date);
                        document.getElementById("day-mercredi-span").textContent = data.mercredi.display;

                        document.getElementById('day-jeudi').setAttribute("data-date", data.jeudi.date);
                        document.getElementById("day-jeudi-span").textContent = data.jeudi.display;

                        document.getElementById('day-vendredi').setAttribute("data-date", data.vendredi.date);
                        document.getElementById("day-vendredi-span").textContent = data.vendredi.display;
                    } catch (e) {
                        console.error("Erreur dans le parsing JSON:", e);
                    }
                } else {
                    console.error("Erreur de statut:", this.status);
                }
            }
        };

        xhr.open("GET", "/controleur/getDaysTop.php", true);
        xhr.send();
    }

    function loadDaysAjax() {
        fetch("/controleur/generateDaysAjax.php")
            .then(response => response.json())
            .then(data => {
                console.log(data);
                const daysContainer = document.getElementById("daysContainer");
                if (daysContainer != null) {
                    daysContainer.innerHTML = data.html;
                    // Réinitialiser les évènements pour les infobulles
                    initializeTooltips();
                } else {
                    console.error("daysContainer non trouvé");
                }
            })
            .catch(error => {
                console.error("Erreur dans la récupération des jours en Ajax:", error);
            });
    }

    // Fonction pour initialiser les infobulles
    function initializeTooltips() {
        document.querySelectorAll('[data-tooltip-target]').forEach(button => {
            const tooltipId = button.getAttribute('data-tooltip-target');
            const tooltip = document.getElementById(tooltipId);

            if (tooltip) {
                button.addEventListener('mouseenter', () => {
                    tooltip.style.display = 'block';
                    tooltip.style.opacity = '1';
                });

                button.addEventListener('mouseleave', () => {
                    tooltip.style.display = 'none';
                    tooltip.style.opacity = '0';
                });
            }
        });
    }

});