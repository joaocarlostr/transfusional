// Flatpickr Initialization - Configuração Completa

/**
 * Calcula feriados brasileiros para qualquer ano
 */
function calcularFeriadosBrasileiros(ano) {
    const feriadosFixos = [
        `${ano}-01-01`, `${ano}-04-21`, `${ano}-05-01`, `${ano}-09-07`,
        `${ano}-10-12`, `${ano}-11-02`, `${ano}-11-15`, `${ano}-11-20`, `${ano}-12-25`
    ];

    // Calcular Páscoa
    const a = ano % 19;
    const b = Math.floor(ano / 100);
    const c = ano % 100;
    const d = Math.floor(b / 4);
    const e = b % 4;
    const f = Math.floor((b + 8) / 25);
    const g = Math.floor((b - f + 1) / 3);
    const h = (19 * a + b - d - g + 15) % 30;
    const i = Math.floor(c / 4);
    const k = c % 4;
    const l = (32 + 2 * e + 2 * i - h - k) % 7;
    const m = Math.floor((a + 11 * h + 22 * l) / 451);
    const mes = Math.floor((h + l - 7 * m + 114) / 31);
    const dia = ((h + l - 7 * m + 114) % 31) + 1;
    const pascoa = new Date(ano, mes - 1, dia);

    const formatarData = (data) => {
        const ano = data.getFullYear();
        const mes = String(data.getMonth() + 1).padStart(2, '0');
        const dia = String(data.getDate()).padStart(2, '0');
        return `${ano}-${mes}-${dia}`;
    };

    const carnaval = new Date(pascoa);
    carnaval.setDate(pascoa.getDate() - 47);
    const carnavalSegunda = new Date(carnaval);
    carnavalSegunda.setDate(carnaval.getDate() - 1);
    const paixaoCristo = new Date(pascoa);
    paixaoCristo.setDate(pascoa.getDate() - 2);
    const corpusChristi = new Date(pascoa);
    corpusChristi.setDate(pascoa.getDate() + 60);

    return [...feriadosFixos, formatarData(carnavalSegunda), formatarData(carnaval),
    formatarData(paixaoCristo), formatarData(corpusChristi)];
}

// Gerar feriados para os próximos 5 anos
const anoAtual = new Date().getFullYear();
let todosFeriados = [];
for (let ano = anoAtual; ano <= anoAtual + 5; ano++) {
    todosFeriados = [...todosFeriados, ...calcularFeriadosBrasileiros(ano)];
}

// Máscara de data para input manual
function aplicarMascaraData(input) {
    input.addEventListener('input', function (e) {
        let valor = e.target.value.replace(/\D/g, '');

        if (valor.length >= 2) {
            valor = valor.substring(0, 2) + '/' + valor.substring(2);
        }
        if (valor.length >= 5) {
            valor = valor.substring(0, 5) + '/' + valor.substring(5, 9);
        }

        e.target.value = valor;
    });
}

// Configuração do Flatpickr
const flatpickrConfig = {
    locale: "pt",
    dateFormat: "d/m/Y",
    allowInput: true,
    showMonths: 1,
    onDayCreate: function (dObj, dStr, fp, dayElem) {
        const date = dayElem.dateObj;
        const dateStr = fp.formatDate(date, "Y-m-d");
        const dayOfWeek = date.getDay();

        // Adicionar classes baseadas no tipo de dia
        if (todosFeriados.includes(dateStr)) {
            dayElem.classList.add("holiday");
        } else if (dayOfWeek === 0) {
            dayElem.classList.add("sunday");
        } else if (dayOfWeek === 6) {
            dayElem.classList.add("saturday");
        } else {
            dayElem.classList.add("weekday");
        }
    }
};

// Inicializar quando o documento estiver pronto
$(document).ready(function () {
    // Inicializar Flatpickr
    const fpInicio = flatpickr("#data_inicio", flatpickrConfig);
    const fpFim = flatpickr("#data_fim", flatpickrConfig);

    // Aplicar máscara nos inputs
    aplicarMascaraData(document.getElementById('data_inicio'));
    aplicarMascaraData(document.getElementById('data_fim'));
});
