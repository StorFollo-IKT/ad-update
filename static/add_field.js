//const last_key = document.getElementById('last_key');
//let last_input = document.getElementById('values' + last_key.value);
let form;
let last_input;


function load()
{
    form = document.getElementById('form');

}
//document.addEventListener("click", load);
form = document.getElementById('form');
console.log(form);
last_input = form.lastChild;
document.getElementById('new_input').addEventListener('change', add_field);

const table = document.getElementById('table');

function add_field(event)
{
    const input = event.target;

    let tr = document.getElementById('new');
    console.log(tr);
    let tr_new = tr.cloneNode(true);
    tr.removeAttribute('id');
    input.removeAttribute('id');
    input.removeEventListener('change', add_field);
    table.appendChild(tr_new);

    const input_new = document.getElementById('new_input');
    input_new.value='';
    input_new.addEventListener("change", add_field);

    /*input_new.value = '';
    let next_key = last_key.value+1;
    input_new.setAttribute('name', 'values['+next_key+']');
    input_new.setAttribute('id', 'values'+next_key);
    last_input.parentNode.appendChild(input_new);
    last_key.value=next_key;
    input_new.lastChild*/
}
