// Botão que abre/fecha o menu
document.getElementById("btnMenu").onclick = () => {
    document.getElementById("sidebar").classList.toggle("hide");
    document.querySelector(".content").classList.toggle("expand");
};
