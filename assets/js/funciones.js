document.addEventListener('DOMContentLoaded', function () {
  $('#tbl').DataTable({
    language: {
      url: '//cdn.datatables.net/plug-ins/1.10.11/i18n/Spanish.json',
    },
    order: [[0, 'desc']],
  });
  $('.confirmar').submit(function (e) {
    e.preventDefault();
    Swal.fire({
      title: 'Esta seguro de eliminar?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'SI, Eliminar!',
    }).then((result) => {
      if (result.isConfirmed) {
        this.submit();
      }
    });
  });
  $('#nom_cliente').autocomplete({
    minLength: 3,
    source: function (request, response) {
      $.ajax({
        url: 'ajax.php',
        dataType: 'json',
        data: {
          q: request.term,
        },
        success: function (data) {
          response(data);
        },
      });
    },
    select: function (event, ui) {
      $('#idcliente').val(ui.item.id);
      $('#nom_cliente').val(ui.item.label);
      $('#tel_cliente').val(ui.item.telefono);
      $('#dir_cliente').val(ui.item.direccion);
      $('#ced_cliente').val(ui.item.cedula);
    },
  });
  $('#producto').autocomplete({
    minLength: 3,
    source: function (request, response) {
      $.ajax({
        url: 'ajax.php',
        dataType: 'json',
        data: {
          pro: request.term,
        },
        success: function (data) {
          console.log({ data });
          response(data);
        },
      });
    },
    select: function (event, ui) {
      $('#id').val(ui.item.id);
      $('#cantidad_elegida').val(ui.item.cantidad_elegida);
      $('#producto').val(ui.item.value);
      $('#precio').val(ui.item.precio);
      $('#existencia').val(ui.item.existencia);
      $('#cantidad').focus();
    },
  });

  $('#btn_generar').click(function (e) {
    e.preventDefault();
    var rows = $('#tblDetalle tr').length;
    if (rows > 2) {
      var action = 'procesarVenta';
      var id = $('#idcliente').val();
      $.ajax({
        url: 'ajax.php',
        async: true,
        data: {
          procesarVenta: action,
          id: id,
          metodo_pago: $('#metodo_pago').val(),
        },
        success: function (response) {
          console.log(response);
          const res = JSON.parse(response);
          if (response != 'error') {
            Swal.fire({
              position: 'top-center',
              icon: 'success',
              title: 'Venta Generada',
              showConfirmButton: false,
              timer: 2000,
            });
            setTimeout(() => {
              generarPDF(res.id_cliente, res.id_venta, $('#metodo_pago').val());
              location.reload();
            }, 300);
          } else {
            Swal.fire({
              position: 'top-center',
              icon: 'error',
              title: 'Error al generar la venta',
              showConfirmButton: false,
              timer: 2000,
            });
          }
        },
        error: function (error) {},
      });
    } else {
      Swal.fire({
        position: 'top-end',
        icon: 'warning',
        title: 'No hay producto para generar la venta',
        showConfirmButton: false,
        timer: 2000,
      });
    }
  });
  if (document.getElementById('detalle_venta')) {
    listar();
  }
});

function calcularPrecio(e) {
  e.preventDefault();
  const cant = $('#cantidad').val();
  const precio = $('#precio').val();
  const total = cant * precio;
  const existencia = $('#existencia').val();
  const cantidad_elegida = $('#cantidad_elegida').val();
  $('#sub_total').val(total);
  if (cant > 0 && cant != '') {
    console.log(existencia - cantidad_elegida);
    if (existencia && existencia - cantidad_elegida >= cant) {
      const id = $('#id').val();
      registrarDetalle(e, id, cant, precio);
      $('#producto').focus();
    } else {
      Swal.fire({
        position: 'top-center',
        icon: 'error',
        title: 'La cantidad de productos seleccionada supera la existencia',
        showConfirmButton: false,
        timer: 2000,
      });
    }
  } else {
    $('#cantidad').focus();
    return false;
  }
}

function listar() {
  let html = '';
  let detalle = 'detalle';
  $.ajax({
    url: 'ajax.php',
    dataType: 'json',
    data: {
      detalle: detalle,
    },
    success: function (response) {
      response.forEach((row) => {
        html += `<tr>
                <td>${row['id']}</td>
                <td>${row['descripcion']}</td>
                <td>${row['cantidad']}</td>
                <td>${row['precio_venta']}</td>
                <td>${row['sub_total']}</td>
                <td><button class="btn btn-danger" type="button" onclick="deleteDetalle(${row['id']})">
                <i class="fas fa-trash-alt"></i></button></td>
                </tr>`;
      });
      document.querySelector('#detalle_venta').innerHTML = html;
      calcular();
    },
  });
}

function registrarDetalle(e, id, cant, precio) {
  if (document.getElementById('producto').value != '') {
    if (id != null) {
      let action = 'regDetalle';
      console.log({ id });
      const idParsed = parseInt(id);
      console.log(idParsed);
      $.ajax({
        url: 'ajax.php',
        type: 'POST',
        dataType: 'json',
        data: {
          id: idParsed,
          cant: cant,
          regDetalle: action,
          precio: precio,
        },
        success: function (response) {
          if (response == 'registrado') {
            $('#cantidad').val('');
            $('#precio').val('');
            $('#producto').val('');
            $('#sub_total').val('');
            $('#producto').focus();
            listar();
            Swal.fire({
              position: 'top-center',
              icon: 'success',
              title: 'Producto Ingresado',
              showConfirmButton: false,
              timer: 2000,
            });
          } else if (response == 'actualizado') {
            $('#cantidad').val('');
            $('#precio').val('');
            $('#producto').val('');
            $('#producto').focus();
            listar();
            Swal.fire({
              position: 'top-center',
              icon: 'success',
              title: 'Producto Actualizado',
              showConfirmButton: false,
              timer: 2000,
            });
          } else {
            $('#id').val('');
            $('#cantidad').val('');
            $('#precio').val('');
            $('#producto').val('');
            $('#producto').focus();
            Swal.fire({
              position: 'top-end',
              icon: 'error',
              title: response,
              showConfirmButton: false,
              timer: 2000,
            });
          }
        },
      });
    }
  }
}

function deleteDetalle(id) {
  let detalle = 'Eliminar';
  $.ajax({
    url: 'ajax.php',
    data: {
      id: id,
      delete_detalle: detalle,
    },
    success: function (response) {
      if (response == 'restado') {
        Swal.fire({
          position: 'top-center',
          icon: 'success',
          title: 'Producto Descontado',
          showConfirmButton: false,
          timer: 2000,
        });
        document.querySelector('#producto').value = '';
        document.querySelector('#producto').focus();
        listar();
      } else if (response == 'ok') {
        Swal.fire({
          position: 'top-end',
          icon: 'success',
          title: 'Producto Eliminado',
          showConfirmButton: false,
          timer: 2000,
        });
        document.querySelector('#producto').value = '';
        document.querySelector('#producto').focus();
        listar();
      } else {
        Swal.fire({
          position: 'top-end',
          icon: 'error',
          title: 'Error al eliminar el producto',
          showConfirmButton: false,
          timer: 2000,
        });
      }
    },
  });
}

function calcular() {
  // obtenemos todas las filas del tbody
  var filas = document.querySelectorAll('#tblDetalle tbody tr');

  var total = 0;

  // recorremos cada una de las filas
  filas.forEach(function (e) {
    // obtenemos las columnas de cada fila
    var columnas = e.querySelectorAll('td');

    // obtenemos los valores de la cantidad y importe
    var importe = parseFloat(columnas[6].textContent);

    subtotal += importe;
  });

  var iva = subtotal * 0.16;
  var total = subtotal + iva;
  // mostramos la suma total
  var filas = document.querySelectorAll('#tblDetalle tfoot tr td');
  filas[1].textContent = total.toFixed(2);
}

function generarPDF(cliente, id_venta, metodo) {
  url = 'pdf/generar.php?cl=' + cliente + '&v=' + id_venta + '&met=' + metodo;
  window.open(url, '_blank');
}
if (document.getElementById('stockMinimo')) {
  const action = 'sales';
  $.ajax({
    url: 'chart.php',
    type: 'POST',
    data: {
      action,
    },
    async: true,
    success: function (response) {
      if (response != 0) {
        var data = JSON.parse(response);
        var nombre = [];
        var cantidad = [];
        for (var i = 0; i < data.length; i++) {
          nombre.push(data[i]['descripcion']);
          cantidad.push(data[i]['existencia']);
        }
        var ctx = document.getElementById('stockMinimo');
        var myPieChart = new Chart(ctx, {
          type: 'pie',
          data: {
            labels: nombre,
            datasets: [
              {
                data: cantidad,
                backgroundColor: ['#024A86', '#E7D40A', '#581845', '#C82A54', '#EF280F', '#8C4966', '#FF689D', '#E36B2C', '#69C36D', '#23BAC4'],
              },
            ],
          },
        });
      }
    },
    error: function (error) {
      console.log(error);
    },
  });
}
if (document.getElementById('ProductosVendidos')) {
  const action = 'polarChart';
  $.ajax({
    url: 'chart.php',
    type: 'POST',
    async: true,
    data: {
      action,
    },
    success: function (response) {
      if (response != 0) {
        var data = JSON.parse(response);
        var nombre = [];
        var cantidad = [];
        for (var i = 0; i < data.length; i++) {
          nombre.push(data[i]['descripcion']);
          cantidad.push(data[i]['cantidad']);
        }
        var ctx = document.getElementById('ProductosVendidos');
        var myPieChart = new Chart(ctx, {
          type: 'doughnut',
          data: {
            labels: nombre,
            datasets: [
              {
                data: cantidad,
                backgroundColor: ['#C82A54', '#EF280F', '#23BAC4', '#8C4966', '#FF689D', '#E7D40A', '#E36B2C', '#69C36D', '#581845', '#024A86'],
              },
            ],
          },
        });
      }
    },
    error: function (error) {
      console.log(error);
    },
  });
}

function btnCambiar(e) {
  e.preventDefault();
  const actual = document.getElementById('actual').value;
  const nueva = document.getElementById('nueva').value;
  if (actual == '' || nueva == '') {
    Swal.fire({
      position: 'top-end',
      icon: 'error',
      title: 'Los campos estan vacios',
      showConfirmButton: false,
      timer: 2000,
    });
  } else {
    const cambio = 'pass';
    $.ajax({
      url: 'ajax.php',
      type: 'POST',
      data: {
        actual: actual,
        nueva: nueva,
        cambio: cambio,
      },
      success: function (response) {
        if (response == 'ok') {
          Swal.fire({
            position: 'top-end',
            icon: 'success',
            title: 'Contraseña modificado',
            showConfirmButton: false,
            timer: 2000,
          });
          document.querySelector('#frmPass').reset();
          $('#nuevo_pass').modal('hide');
        } else if (response == 'dif') {
          Swal.fire({
            position: 'top-end',
            icon: 'error',
            title: 'La contraseña actual incorrecta',
            showConfirmButton: false,
            timer: 2000,
          });
        } else {
          Swal.fire({
            position: 'top-end',
            icon: 'error',
            title: 'Error al modificar la contraseña',
            showConfirmButton: false,
            timer: 2000,
          });
        }
      },
    });
  }
}

function editarCliente(id) {
  const action = 'editarCliente';
  $.ajax({
    url: 'ajax.php',
    type: 'GET',
    async: true,
    data: {
      editarCliente: action,
      id: id,
    },
    success: function (response) {
      const datos = JSON.parse(response);
      $('#nombre').val(datos.nombre);
      $('#telefono').val(datos.telefono);
      $('#direccion').val(datos.direccion);
      $('#cedula').val(datos.cedula);
      $('#id').val(datos.idcliente);
      $('#btnAccion').val('Modificar');
    },
    error: function (error) {
      console.log(error);
    },
  });
}

function editarUsuario(id) {
  const action = 'editarUsuario';
  $.ajax({
    url: 'ajax.php',
    type: 'GET',
    async: true,
    data: {
      editarUsuario: action,
      id: id,
    },
    success: function (response) {
      const datos = JSON.parse(response);
      $('#nombre').val(datos.nombre);
      $('#usuario').val(datos.usuario);
      $('#correo').val(datos.correo);
      $('#cedula').val(datos.cedula);
      $('#id').val(datos.idusuario);
      $('#btnAccion').val('Modificar');
    },
    error: function (error) {
      console.log(error);
    },
  });
}

function editarProducto(id) {
  const action = 'editarProducto';
  $.ajax({
    url: 'ajax.php',
    type: 'GET',
    async: true,
    data: {
      editarProducto: action,
      id: id,
    },
    success: function (response) {
      const datos = JSON.parse(response);
      $('#codigo').val(datos.codigo);
      $('#producto').val(datos.descripcion);
      $('#precio').val(datos.precio);
      $('#id').val(datos.codproducto);
      $('#tipo').val(datos.id_tipo);
      $('#presentacion').val(datos.id_presentacion);
      $('#laboratorio').val(datos.id_lab);
      $('#vencimiento').val(datos.vencimiento);
      $('#cantidad').val(datos.existencia);
      if (datos.vencimiento != '0000-00-00') {
        $('#accion').prop('checked', true);
      } else {
        $('#accion').prop('checked', false);
      }
      $('#btnAccion').val('Modificar');
    },
    error: function (error) {
      console.log(error);
    },
  });
}

function limpiar() {
  $('#formulario')[0].reset();
  $('#id').val('');
  $('#btnAccion').val('Registrar');
}
function editarTipo(id) {
  const action = 'editarTipo';
  $.ajax({
    url: 'ajax.php',
    type: 'GET',
    async: true,
    data: {
      editarTipo: action,
      id: id,
    },
    success: function (response) {
      const datos = JSON.parse(response);
      $('#nombre').val(datos.tipo);
      $('#id').val(datos.id);
      $('#btnAccion').val('Modificar');
    },
    error: function (error) {
      console.log(error);
    },
  });
}
function editarPresent(id) {
  const action = 'editarPresent';
  $.ajax({
    url: 'ajax.php',
    type: 'GET',
    async: true,
    data: {
      editarPresent: action,
      id: id,
    },
    success: function (response) {
      const datos = JSON.parse(response);
      $('#nombre').val(datos.nombre);
      $('#nombre_corto').val(datos.nombre_corto);
      $('#id').val(datos.id);
      $('#btnAccion').val('Modificar');
    },
    error: function (error) {
      console.log(error);
    },
  });
}
function editarLab(id) {
  const action = 'editarLab';
  $.ajax({
    url: 'ajax.php',
    type: 'GET',
    async: true,
    data: {
      editarLab: action,
      id: id,
    },
    success: function (response) {
      const datos = JSON.parse(response);
      $('#laboratorio').val(datos.laboratorio);
      $('#direccion').val(datos.direccion);
      $('#id').val(datos.id);
      $('#btnAccion').val('Modificar');
    },
    error: function (error) {
      console.log(error);
    },
  });
}

function validarStock(event) {
  const cantidad = parseInt(document.getElementById('cantidad').value) || 0;
  const stockDisponible = parseInt(document.getElementById('stock_disponible').value) || 0;

  if (cantidad > stockDisponible) {
    alert(`No puedes comprar más de ${stockDisponible} unidades.`);
    document.getElementById('cantidad').value = stockDisponible; // Ajustar al máximo permitido
    if (event && event.key === 'Enter') {
      event.preventDefault(); // Prevenir el comportamiento predeterminado (como el envío del formulario)
    }
  }
}

function validarCantidad(input) {
  const maxLength = 3; // Límite de 3 caracteres

  // Verifica si la longitud del valor es mayor que 3
  if (input.value.length > maxLength) {
    input.value = input.value.slice(0, maxLength); // Limita el valor a 3 caracteres
    alert('La cantidad no puede exceder de 3 dígitos.');
  }

  // Asegura que el valor no sea negativo
  if (input.value < 1) {
    input.value = ''; // Limpia el campo si el valor es menor a 1
    alert('La cantidad debe ser un número mayor o igual a 1.');
  }
}

