(function () {
  //  idConfirmar;
  // idModalConfirmar;
  $(function () {
    // $("#myModal").modal(options);
    $("#idConfirmar").on("click", function () {
      $("#idModalConfirmar").modal();
    });
  });
})();

(function () {
  //  idConfirmar;
  // idModalConfirmar;
  $(function () {
    // $("#myModal").modal(options);
    $("#idAnular").on("click", function () {
      $("#idModalAnular").modal();
    });
  });
})();

(function () {
  // Reportar daños;
  $(function () {
    $("#idReportarDaños").on("click", function () {
      setTimeout(() => {
        $("#idModalReportar").modal();
      }, 5000);
    });
  });
})();

(function () {
  // Reportar daños;
  $(function () {
    $("#idBotonConfirmar").on("click", function () {
      setTimeout(() => {
        $("#idModalCalificar").modal();
      }, 5000);
    });
  });
})();

setTimeout(() => {
  const reportar = document.getElementById("msjReportar");

  reportar.style.display = "none";
}, 6000);

setTimeout(() => {
  const anular = document.getElementById("msjAnular");
  anular.style.display = "none";
}, 6000);

setTimeout(() => {
  const calificar = document.getElementById("msjCalificar");

  calificar.style.display = "none";
}, 6000);
