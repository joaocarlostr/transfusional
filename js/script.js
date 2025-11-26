//******************************************************************************
// SCRIPS DAS PAGINAS
//******************************************************************************

//******************************************************************************
// VALIDACAO DOS CAMPOS DOS FORMULARIOS
//******************************************************************************

$(document).ready(function () {
    //******************************************************************************
    // ARQUIVO: cadastro_paciente.php
    //******************************************************************************      

    // nome do formulario
    $("form[name='cadastro_paciente.php']").validate({
        // Define as regras
        rules: {
            // Nome será obrigatório (required) e tera tamanho minimo (minLength)
            nome:            { required: true }, // para mais campos, separe por virgula
            cpf:             { number: true }, 
            sexo:            { required: true },
            mae:             { required: true },
            abo:             { required: true },
            rh:              { required: true },
            data_nascimento: { required: true },
            setor:           { required: true },
            leito:           { required: true },
            internacao:      { required: true}, 
            num_sus:         { number: true, minlength: 15 },
            celular:         { number: true, minlength: 9 },
            observacao:      { required: true},
            data_requisicao: { required: true},
            responsavel:     { required: true},
        },

        // Define as mensagens de erro para cada regra
        messages: {
            nome:            { required: "Digite o nome do paciente" }, 
            cpf:             { minlength: "CPF com 11 dígitos", number: "Esse campo só pode conter números" },
            sexo:            { required: "Selecione o sexo"},
            mae:             { required: "Informe o nome da m&atilde;e" },
            abo:             { required: "Selecione o Abo" },
            rh:              { required: "Selecione o Rh" },
            data_nascimento: { required: "Informe a data de nascimento" },
            setor:           { required: "Selecione o setor" },
            leito:           { required: "Informe o leito" },
            internacao:      { required: "Digite o nome do hospital"},
            num_sus:         { minlength: "CNS com 15 dígitos", number: "Esse campo só pode conter números" },
            celular:         { minlength: "Celular com 9 dígitos, iniciando-se com 0", number: "Esse campo só pode conter números" },
            observacao:      { required:"Digite uma observação sobre o paciente"},
            dataRequisicao:  { required: "Coloque a data"},
            responsavel:     { required: "Digite o nome do responsavel por esse cadastro"} // para mais campos separe por virgula
        }

    }); //fecha o formulario a ser validado   
    //******************************************************************************
    // ARQUIVO: cadastrar_bolsa.php
    //******************************************************************************      

    $("form[name='cadastrar_bolsa.php']").validate({
        // Define as regras
        rules: {
            nome_paciente:     { required: true }, // para mais campos, separe por virgula
            sexo:              { required: true },
            dt_nasc:           { required: true },
            racacor:           { required: true },
            estado_civil:      { required: true },
            mae:               { required: true },
            num_casa:          { required: true },
            enderecodigitado:  { required: true },
            municipiodigitado: { required: true },
            ufdigitado:        { required: true, minlength: 2 },
            ddd:               { number: true, minlength: 2 },
            fone:              { number: true, minlength: 9 },
            cns:               { number: true, minlength: 15 }
        },

        // Define as mensagens de erro para cada regra
        messages: {
            nome_paciente:     { required: "Digite o nome do paciente" }, // para mais campos separe por virgula
            dt_nasc:           { required: "Informe a data de nascimento" },
            sexo:              { required: "Selecione o sexo" },
            racacor:           { required: "Selecione a &ccedil;a/cor" },
            estado_civil:      { required: "Selecione o estado civil" },
            mae:               { required: "Informe o nome da m&atilde;e" },
            num_casa:          { required: "Informe o n&uacute;mero da casa, caso n&atilde;o tenha, digite S/N." },
            enderecodigitado:  { required: "Informe o endere&ccedil;o e o bairro" },
            municipiodigitado: { required: "Informe a cidade" },
            ufdigitado: {
                required: "Informe o estado",
                minlength: "O estado deve conter, no m&iacute;nimo, 2 caracteres"
            },
            ddd:  { minlength: "DDD com 2 dígitos", number: "Esse campo só pode conter números" },
            fone: { minlength: "Fone com 9 dígitos, iniciando-se com 0", number: "Esse campo só pode conter números" },
            cns:  { minlength: "CNS com 15 dígitos", number: "Esse campo só pode conter números" }
        }

    }); //fecha o formulario a ser validado

    //******************************************************************************
    // ARQUIVO: bolsas_devolvidas.php
    //******************************************************************************
    $("#bolsas_devolvidas.php").validate({
        // Define as regras
        rules: {
            diagnostico:  { required: true }, // para mais campos, separe por virgula
            tipo_alta:    { required: true },
            usdestino:    { required: true },
            profissional: { required: true }
        },

        // Define as mensagens de erro para cada regra
        messages: {
            diagnostico:  { required: "Informe o diagn&oacute;stico" }, // para mais campos, separe por virgula
            tipo_alta:    { required: "Selecione o tipo de alta" },
            usdestino:    { required: "Selecione o destino" },
            profissional: { required: "Selecione o m&eacute;dico que deu alta" }
        }

    }); //fecha o formulario a ser validado  

    //******************************************************************************
    // ARQUIVO: reacao_transfusional.php
    //******************************************************************************
    $("#reacao_transfusional").validate({
        // Define as regras
        rules: {
            diagnostico:  { required: true }, // para mais campos, separe por virgula
            tipo_alta:    { required: true },
            usdestino:    { required: true },
            profissional: { required: true }
        },

        // Define as mensagens de erro para cada regra
        messages: {
            diagnostico:  { required: "Informe o diagn&oacute;stico" }, // para mais campos, separe por virgula
            tipo_alta:    { required: "Selecione o tipo de alta" },
            usdestino:    { required: "Selecione o destino" },
            profissional: { required: "Selecione o m&eacute;dico que deu alta" }
        }

    }); //fecha o formulario a ser validado  
});

//******************************************************************************
// COLOCA TUDO EM MAIUSCULAS E SEM ACENTO - funcao em JQuery
//******************************************************************************

function noTilde(objResp) {
    var varString       = objResp.value;
    var stringAcentos   = 'abcçdefghijklmnopqrstuvxywzàâêôûãõáéíóúüÀÂÊÔÛÃÕÁÉÍÓÚÜ[]';
    var stringSemAcento = 'ABCÇDEFGHIJKLMNOPQRSTUVXYWZAAEOUAOAEIOUUAAEOUAOAEIOUU__';

    // Obter a posição atual do cursor
    var start = objResp.selectionStart;
    var end   = objResp.selectionEnd;

    var varRes = '';
    for (var i = 0; i < varString.length; i++) {

        var cString = varString.charAt(i);
        var index   = stringAcentos.indexOf(cString);

        if (index !== -1) {
            cString = stringSemAcento.charAt(index);
        }

        varRes += cString;
    }

    // Verificar se o valor realmente mudou
    if (objResp.value !== varRes) {
        objResp.value = varRes;
        // Restaurar a posição do cursor
        objResp.setSelectionRange(start, end);
    }
}

$(document).ready(function () {
    $("input[type=text]").on('input', function () {
        noTilde(this);
    });
});



//******************************************************************************
// FUNÇÃO PARA IMPEDIR CAMPOS EM BRANCO - sem uso no momento
//******************************************************************************

// function valida_cadastro() {
//     var d = document.cadastro_paciente;

//     if (d.nome_paciente.value.trim() === "") {
//         alert("Por favor, informe o NOME do paciente.");
//         d.nome_paciente.focus();
//         return false;
//     }

//     if (d.sexo.value.trim() === "") {
//         alert("Por favor, selecione o SEXO do paciente.");
//         d.sexo.focus();
//         return false;
//     }

//     // Adicionar mais verificações 

//     return true;
// }


//******************************************************************************
// MASCARA PARA DATAS - formatar para dd/mm/aaaa hh:mm
//******************************************************************************

function dateMaskH(inputData, event) {
    var tecla = event.keyCode || event.which;

    if ((tecla >= 47 && tecla < 58) || tecla == 8 || tecla == 0) {
        var data = inputData.value;

        if ([2, 5].includes(data.length)) {
            data += '/';
        }

        if (data.length === 10) {
            data += ' ';
        }

        if ([13, 16].includes(data.length)) {
            data += ':';
        }

        // Limitar o ano a 4 dígitos
        if (data.length === 11) {
            data = data.substring(0, 10);
        }

        inputData.value = data;
    } else {
        return false;
    }
}


//******************************************************************************
// PERMITIR APENAS NUMEROS NO INPUT
//******************************************************************************

function apenasNumeros(input) {
    input.value = input.value.replace(/[^0-9]/g, '');
}


//******************************************************************************
// MASCARA E VALIDAÇÃO PARA CPF
//******************************************************************************

function formatarCPF(input) {

    // Remove caracteres não numéricos
    let cpf = input.value.replace(/\D/g, ''); 

    // Verifica se o tamanho do CPF é válido
    if (cpf.length == 11) {
        // Formatação do CPF: XXX.XXX.XXX-XX
        input.value = cpf.replace(/^(\d{3})(\d{3})(\d{3})(\d{2})$/, '$1.$2.$3-$4'); 
    }

    // Formatar o CPF conforme o número de dígitos
    // if (cpf.length > 3 && cpf.length <= 6) {
    //     input.value = cpf.substring(0, 3) + '.' + cpf.substring(3);
    // }else if (cpf.length > 6 && cpf.length <= 9) {
    //     input.value = cpf.substring(0, 3) + '.' + cpf.substring(3, 6) + '.' + cpf.substring(6);
    // }else if (cpf.length > 9) {
    //     input.value = cpf.substring(0, 3) + '.' + cpf.substring(3, 6) + '.' + cpf.substring(6, 9) + '-' + cpf.substring(9);
    // }
    
}

function isCPFValido(cpf) {
    if (cpf == "00000000000") {
        return true; // paciente sem documento
    }else if (/^(\d)\1{10}$/.test(cpf)) {
        return false; // CPF com todos os dígitos iguais
    }

    var soma = 0;
    var resto;

    for (var i = 0; i < 9; i++) {
        soma += parseInt(cpf.charAt(i)) * (10 - i);
    }

    resto = (soma * 10) % 11;

    if (resto === 10 || resto === 11) {
        resto = 0;
    }

    if (resto !== parseInt(cpf.charAt(9))) {
        return false;
    }

    soma = 0;

    for (var i = 0; i < 10; i++) {
        soma += parseInt(cpf.charAt(i)) * (11 - i);
    }

    resto = (soma * 10) % 11;

    if (resto === 10 || resto === 11) {
        resto = 0;
    }

    if (resto !== parseInt(cpf.charAt(10))) {
        return false;
    }

    return true;
}

function validarCPF(input) {
    var cpf = input.value.replace(/\D/g, ''); // Remove caracteres não numéricos
    
    if(cpf.length !== 0){
        if (cpf.length !== 11 || !isCPFValido(cpf)) {
            alert("CPF inválido!"); 
            input.value = ""; // Limpa o campo
            setTimeout(function(){input.focus();}, 1);
        }
    }
}


//******************************************************************************
// FUNÇÕES PARA MASCARAR E VALIDAR O NUMERO DE CELULAR - sem uso no momento
//******************************************************************************
 
// function formatarEValidarCelular(input) {
//     var numeroCelular = input.value.replace(/\D/g, ''); // Remove caracteres não numéricos

//     if (numeroCelular.length <= 10) {
//         // Formata para (XX) XXXX-XXXX
//         input.value = numeroCelular.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3'); // mudei para 5, arrumar certo depois
//     } else {
//         // Formata para (XX) XXXXX-XXXX
//         input.value = numeroCelular.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
//     }

//     if (numeroCelular.length === 14) { // contei os numeros e a mascara de formatacao
//         // Verifica se o número de celular é válido
//         alert(validarCelular(numeroCelular) ? 'Número de celular válido!' : 'Número de celular inválido!');
//     }
// }

// function validarCelular(numeroCelular) {
//     // Verifica se o número de celular possui 11 dígitos e inicia com 9
//     return /^9\d{10}$/.test(numeroCelular);
// }

//******************************************************************************
// FUNÇÕES PARA MASCARAR N° DE NOTIFICAÇÃO DE REAÇÕES TRANSFUSIONAIS
//******************************************************************************

function formatarnotificacao(input) {
    var fit = input.value.replace(/\D/g, ''); // Remove caracteres não numéricos
    input.setCustomValidity('Número da notificação inválido');

    if (fit.length == 12) {
        // Formata para XXXX.XX.XXXXXX
        input.value = fit.replace(/(\d{4})(\d{2})(\d{6})/, '$1.$2.$3');  
        input.setCustomValidity('');
    } 
}

//******************************************************************************
// FUNÇÕES PARA MASCARAR E VALIDAR O NUMERO DO SUS/CNS DO PACIENTE
//******************************************************************************
 
function validaEFormataCns(input) {
    // Remove caracteres não numéricos
    let cns = input.value.replace(/\D/g, '');

    if(input.value.length != 0){
        input.setCustomValidity('Número do CNS inválido');

        // Verifica se o tamanho do CNS é válido
        if (cns.length == 15) {
            input.setCustomValidity('');

            // Formatação do CNS: XXX.XXXX.XXXX.XXXX
            let cnsFormatado = cns.replace(/^(\d{3})(\d{4})(\d{4})(\d{4})$/, '$1.$2.$3.$4');

            // Atualiza o valor do campo com o CNS formatado
            input.value = cnsFormatado;
        }
    }
}

//******************************************************************************
// FUNÇÕES PARA VALIDAR O NUMERO DO SUS DA BOLSA
//******************************************************************************
 
function validarSUSBolsa(input){
    apenasNumeros(input);
    input.setCustomValidity('Número do sus da bolsa precisa ter 11 números');

    if(input.value.length == 11){
        input.setCustomValidity('');
    }
}

//******************************************************************************
// FUNÇÃO PARA ADICIONAR B NO INÍCIO DO CAMPO
//******************************************************************************

function formatarCodigo(input) {
    // Verifica se o campo começa com "B-"
    if (!input.value.startsWith("B")) {
        input.value = "B" + input.value;
    }
    
    input.setCustomValidity('O número da bolsa precisa ter 12 números');
    var num_bolsa = input.value.replace(/\D/g, ''); //pega os numeros do campo

    // Limita o comprimento total do código
    if (num_bolsa.length == 12) {
        input.value = input.value.slice(0, 13);
        input.setCustomValidity('');
    }
}

//******************************************************************************
// SCRIPTS DA PAGINA **buscar_paciente.php**
//******************************************************************************

function filtrarECruzarDados() {
    var nomeCompleto = document.getElementById('nome_completo').value.toUpperCase();
    var nomeMae      = document.getElementById('nome_mae').value.toUpperCase();
    var cpf          = document.getElementById('cpf').value.toUpperCase();
    var dtNasc       = document.getElementById('dt_nasc').value.toUpperCase();
    var dtRequisicao = document.getElementById('dt_requisicao').value.toUpperCase();
    var table        = document.querySelector("table");
    var tr           = table.getElementsByTagName("tr");

    for (var i = 1; i < tr.length; i++) {
        var nomeCol         = tr[i].getElementsByTagName("td")[1]; // Coluna do nome do paciente
        var nomeMaeCol      = tr[i].getElementsByTagName("td")[2]; // Coluna do nome da mãe
        var cpfCol          = tr[i].getElementsByTagName("td")[3]; // Coluna do CPF
        var dtNascCol       = tr[i].getElementsByTagName("td")[0]; // Coluna da data de nascimento
        var dtRequisicaoCol = tr[i].getElementsByTagName("td")[8]; // Coluna da data de requisição

        if (nomeCol && nomeMaeCol && cpfCol && dtNascCol && dtRequisicaoCol) {
            var nomeCompletoValue = nomeCol.textContent || nomeCol.innerText;
            var nomeMaeValue      = nomeMaeCol.textContent || nomeMaeCol.innerText;
            var cpfValue          = cpfCol.textContent || cpfCol.innerText;
            var dtNascValue       = dtNascCol.textContent || dtNascCol.innerText;
            var dtRequisicaoValue = dtRequisicaoCol.textContent || dtRequisicaoCol.innerText;

            // Verifica se os termos coincidem com os campos correspondentes
            var nomeCompletoCoincide = nomeCompletoValue.toUpperCase().indexOf(nomeCompleto) > -1;
            var nomeMaeCoincide      = nomeMaeValue.toUpperCase().indexOf(nomeMae) > -1;
            var cpfCoincide          = cpfValue.toUpperCase().indexOf(cpf) > -1;
            var dtNascCoincide       = dtNascValue.toUpperCase().indexOf(dtNasc) > -1;
            var dtRequisicaoCoincide = dtRequisicaoValue.toUpperCase().indexOf(dtRequisicao) > -1;

            // Verifica se todos os termos coincidem
            if (nomeCompletoCoincide && nomeMaeCoincide && cpfCoincide && dtNascCoincide && dtRequisicaoCoincide) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}

function toggleAdvancedSearch() {
    const avancadaButton  = document.querySelector('.avancada-button');
    const camposAvancados = document.getElementById('campos-avancados');
    const container       = document.getElementById('corpo-pacientes');

    avancadaButton.addEventListener('click', function () {
        const width = window.innerWidth;

        if (camposAvancados.style.display === 'none' || !camposAvancados.classList.contains('expanded')) {
            camposAvancados.style.display = 'block';
            avancadaButton.innerText      = 'Simples';

            avancadaButton.classList.add('expanded'); // Adiciona a classe 'expanded'
            camposAvancados.classList.add('expanded'); // Adiciona a classe 'expanded'

            if (width <= 768) {
                container.style.height = '1100px'; // Altura para telas até 768px
            } else if (width <= 1280) {
                container.style.height = '1520px'; // Altura para telas até 1280px
            } else {
                container.style.height = '1300px'; // Altura para telas maiores
            }
        } else {
            camposAvancados.style.display = 'none';
            avancadaButton.innerText      = 'Avançada';

            avancadaButton.classList.remove('expanded'); // Remove a classe 'expanded'
            camposAvancados.classList.remove('expanded'); // Remove a classe 'expanded'

            if (width <= 768) {
                container.style.height = '1000px'; // Altura para telas até 768px
            } else if (width <= 1280) {
                container.style.height = '1420px'; // Altura para telas até 1280px
            } else {
                container.style.height = '1250px'; // Altura para telas maiores
            }
        }
    });
}

//******************************************************************************
// PENSAR AINDA COMO COLOCAR NAS PAGINAS - sem uso no momento
//******************************************************************************

// function selecionar_tudo() {
//     for (i = 0; i < document.escala_aprovadas.elements.length; i++)
//         if (document.escala_aprovadas.elements[i].type == "checkbox")
//             document.escala_aprovadas.elements[i].checked = 1
// }

// function deselecionar_tudo() {
//     for (i = 0; i < document.escala_aprovadas.elements.length; i++)
//         if (document.escala_aprovadas.elements[i].type == "checkbox")
//             document.escala_aprovadas.elements[i].checked = 0
// } 


//******************************************************************************
// SCRIPTS DE AJUDA
//******************************************************************************

// Função para abrir a janela de ajuda
function openHelp() {
    // Exibir o modal de ajuda
    $('#helpModal').modal('show');
}

// Adicione event listener ao botão flutuante de ajuda
document.getElementById("helpButton").addEventListener("click", openHelp);

//******************************************************************************
// SCRIPTS DE ANEXAR ARQUIVO NA PAGINA DE INFORMAÇOES
//******************************************************************************

