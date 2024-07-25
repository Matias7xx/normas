@extends('layouts.app')
@section('page-title')
    Lista de Normas
@endsection
@section('header-content')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark">Lista de Normas</h1>
        </div><!-- /.col -->
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Pesquisar normas</a></li>
            </ol>
        </div><!-- /.col -->
    </div>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3>Lista de <b>NORMAS</b> por <b> TIPO</b></h3>
                    </div>
                    <div class="card-body">
                        <section class="content">
                            <div class="row">
                                @php
                                    $contador = 0;
                                    $array_color = [
                                        'rgb(57, 107, 254)',
                                        'rgb(228, 184, 27)',
                                        'rgb(247, 105, 105)',
                                        'rgb(105, 247, 110)',
                                        'rgb(212, 132, 244)',
                                        'rgb(132, 194, 244)',
                                    ];
                                @endphp

                                @foreach ($normas_por_tipo as $key => $normas)
                                    <div class="col-12" id="accordion">
                                        <div class="card card-default card-outline">
                                            <a class="d-block w-100" data-toggle="collapse"
                                                href="#collapse{{ $contador }}">
                                                <div class="card-header"
                                                    style="background-color: {{ $array_color[$contador] }}; ">
                                                    <h4 class="card-title w-100">
                                                        <b style='color:rgb(244, 247, 249);'>{{ mb_strtoupper($key) }}</b>
                                                    </h4>
                                                </div>
                                            </a>
                                            <div id="collapse{{ $contador }}"
                                                class="collapse {{ $contador == 0 ? 'show' : '' }}"
                                                data-parent="#accordion">
                                                <div class="card-body">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Ord</th>
                                                                <th>Norma</th>
                                                                <th>Resumo</th>
                                                                <th>Órgão</th>
                                                                <th>Documento</th>
                                                                <th>**</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($normas as $key => $norma)
                                                                <tr>
                                                                    <td>{{ $key + 1 }}</td>
                                                                    <td>{{ $norma->descricao }}</td>
                                                                    <td>{{ $norma->resumo }}</td>
                                                                    <td>{{ $norma->orgao->orgao }}</td>
                                                                    <td><a
                                                                            href='javascript:abrirPagina("../storage/normas/{{ $norma->anexo }}",600,600);'><button
                                                                                class='btn btn-danger'>
                                                                                <nobr><i class='fas fa-file-pdf'></i> Anexo
                                                                                </nobr>
                                                                            </button></a></td>
                                                                    <td><a
                                                                            href="{{ route('normas.norma_edit', $norma->id) }}"><button
                                                                                class='btn btn-success'>
                                                                                <nobr><i class='fas fa-edit'></i> Editar
                                                                                </nobr>
                                                                            </button></a></td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @php
                                        $contador++;
                                    @endphp
                                @endforeach

                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script></script>

    <script>
        $('#estado').on('change', function() {
            var id = $(this).val();
            $.getJSON('/get-cidades/' + id, function(data, id) {
                $('#cidades').empty();
                $.each(data, function(i, cidade) {
                    $('#cidades').append('<option value="' + cidade.id + '">' + cidade.nome +
                        '</option>');
                });
            });
        });

        $('#cidades').on('change', function() {
            var id = $(this).val();
            $.getJSON('/get-bairros/' + id, function(data, id) {
                $('#bairros').empty();
                $.each(data, function(i, bairro) {
                    $('#bairros').append('<option value="' + bairro.id + '">' + bairro.nome +
                        '</option>');
                });
            });
        });

        $('#superintendencia').on('change', function() {
            var id = $(this).val();
            $.getJSON('/get-seccionais-especializadas/' + id, function(data, id) {
                $('#seccionais_especializadas').empty();
                $('#seccionais_especializadas').append('<option></option>');
                $.each(data, function(i, seccionais_especializadas) {
                    $('#seccionais_especializadas').append('<option value="' +
                        seccionais_especializadas.id_delegacia + '">' +
                        seccionais_especializadas.nome +
                        '</option>');
                });
            });
        });

        $('#seccionais_especializadas').on('change', function() {
            var id = $(this).val();
            $.getJSON('/get-delegacias-select/' + id, function(data, id) {
                $('#delegacias_select').empty();
                $('#delegacias_select').append('<option></option>');
                $.each(data, function(i, delegacias_select) {
                    $('#delegacias_select').append('<option value="' + delegacias_select
                        .id_delegacia + '">' + delegacias_select.nome +
                        '</option>');
                });
            });
        });

        $('#tipo_ocorrencia').on('change', function() {
            var id_ocorrencia = $(this).val();
            $('#ocorrencia').empty();
            $.getJSON('/get-ocorrencia/' + id_ocorrencia, function(ocorrencias, id) {
                $('#ocorrencia').append('<option></option>');
                $.each(ocorrencias, function(i, ocorrencia) {
                    $('#ocorrencia').append('<option value="' + ocorrencia.descricao + '">' +
                        ocorrencia.descricao + '</option>');
                });
            });
        });
    </script>

    <script>
        // Initialize function, create initial tokens with itens that are already selected by the user
        function init(element) {
            // Create div that wroaps all the elements inside (select, elements selected, search div) to put select inside
            const wrapper = document.createElement("div");
            wrapper.addEventListener("click", clickOnWrapper);
            wrapper.classList.add("multi-select-component");

            // Create elements of search
            const search_div = document.createElement("div");
            search_div.classList.add("search-container");
            const input = document.createElement("input");
            input.classList.add("selected-input");
            input.setAttribute("autocomplete", "off");
            input.setAttribute("tabindex", "0");
            input.addEventListener("keyup", inputChange);
            input.addEventListener("keydown", deletePressed);
            input.addEventListener("click", openOptions);

            const dropdown_icon = document.createElement("a");
            dropdown_icon.setAttribute("href", "#");
            dropdown_icon.classList.add("dropdown-icon");

            dropdown_icon.addEventListener("click", clickDropdown);
            const autocomplete_list = document.createElement("ul");
            autocomplete_list.classList.add("autocomplete-list")
            search_div.appendChild(input);
            search_div.appendChild(autocomplete_list);
            search_div.appendChild(dropdown_icon);

            // set the wrapper as child (instead of the element)
            element.parentNode.replaceChild(wrapper, element);
            // set element as child of wrapper
            wrapper.appendChild(element);
            wrapper.appendChild(search_div);

            createInitialTokens(element);
            addPlaceholder(wrapper);
        }

        function removePlaceholder(wrapper) {
            const input_search = wrapper.querySelector(".selected-input");
            input_search.removeAttribute("placeholder");
        }

        function addPlaceholder(wrapper) {
            const input_search = wrapper.querySelector(".selected-input");
            const tokens = wrapper.querySelectorAll(".selected-wrapper");
            if (!tokens.length && !(document.activeElement === input_search))
                input_search.setAttribute("placeholder", "---------");
        }


        // Function that create the initial set of tokens with the options selected by the users
        function createInitialTokens(select) {
            let {
                options_selected
            } = getOptions(select);
            const wrapper = select.parentNode;
            for (let i = 0; i < options_selected.length; i++) {
                createToken(wrapper, options_selected[i]);
            }
        }


        // Listener of user search
        function inputChange(e) {
            const wrapper = e.target.parentNode.parentNode;
            const select = wrapper.querySelector("select");
            const dropdown = wrapper.querySelector(".dropdown-icon");

            const input_val = e.target.value;

            if (input_val) {
                dropdown.classList.add("active");
                populateAutocompleteList(select, input_val.trim());
            } else {
                dropdown.classList.remove("active");
                const event = new Event('click');
                dropdown.dispatchEvent(event);
            }
        }


        // Listen for clicks on the wrapper, if click happens focus on the input
        function clickOnWrapper(e) {
            const wrapper = e.target;
            if (wrapper.tagName == "DIV") {
                const input_search = wrapper.querySelector(".selected-input");
                const dropdown = wrapper.querySelector(".dropdown-icon");
                if (!dropdown.classList.contains("active")) {
                    const event = new Event('click');
                    dropdown.dispatchEvent(event);
                }
                input_search.focus();
                removePlaceholder(wrapper);
            }

        }

        function openOptions(e) {
            const input_search = e.target;
            const wrapper = input_search.parentElement.parentElement;
            const dropdown = wrapper.querySelector(".dropdown-icon");
            if (!dropdown.classList.contains("active")) {
                const event = new Event('click');
                dropdown.dispatchEvent(event);
            }
            e.stopPropagation();

        }

        // Function that create a token inside of a wrapper with the given value
        function createToken(wrapper, value) {
            const search = wrapper.querySelector(".search-container");
            // Create token wrapper
            const token = document.createElement("div");
            token.classList.add("selected-wrapper");
            const token_span = document.createElement("span");
            token_span.classList.add("selected-label");
            token_span.innerText = value;
            const close = document.createElement("a");
            close.classList.add("selected-close");
            close.setAttribute("tabindex", "-1");
            close.setAttribute("data-option", value);
            close.setAttribute("data-hits", 0);
            close.setAttribute("href", "#");
            close.innerText = "x";
            close.addEventListener("click", removeToken)
            token.appendChild(token_span);
            token.appendChild(close);
            wrapper.insertBefore(token, search);
        }


        // Listen for clicks in the dropdown option
        function clickDropdown(e) {

            const dropdown = e.target;
            const wrapper = dropdown.parentNode.parentNode;
            const input_search = wrapper.querySelector(".selected-input");
            const select = wrapper.querySelector("select");
            dropdown.classList.toggle("active");

            if (dropdown.classList.contains("active")) {
                removePlaceholder(wrapper);
                input_search.focus();

                if (!input_search.value) {
                    populateAutocompleteList(select, "", true);
                } else {
                    populateAutocompleteList(select, input_search.value);

                }
            } else {
                clearAutocompleteList(select);
                addPlaceholder(wrapper);
            }
        }


        // Clears the results of the autocomplete list
        function clearAutocompleteList(select) {
            const wrapper = select.parentNode;

            const autocomplete_list = wrapper.querySelector(".autocomplete-list");
            autocomplete_list.innerHTML = "";
        }

        // Populate the autocomplete list following a given query from the user
        function populateAutocompleteList(select, query, dropdown = false) {
            const {
                autocomplete_options
            } = getOptions(select);


            let options_to_show;

            if (dropdown)
                options_to_show = autocomplete_options;
            else
                options_to_show = autocomplete(query, autocomplete_options);

            const wrapper = select.parentNode;
            const input_search = wrapper.querySelector(".search-container");
            const autocomplete_list = wrapper.querySelector(".autocomplete-list");
            autocomplete_list.innerHTML = "";
            const result_size = options_to_show.length;

            if (result_size == 1) {

                const li = document.createElement("li");
                li.innerText = options_to_show[0];
                li.setAttribute('data-value', options_to_show[0]);
                li.addEventListener("click", selectOption);
                autocomplete_list.appendChild(li);
                if (query.length == options_to_show[0].length) {
                    const event = new Event('click');
                    li.dispatchEvent(event);

                }
            } else if (result_size > 1) {

                for (let i = 0; i < result_size; i++) {
                    const li = document.createElement("li");
                    li.innerText = options_to_show[i];
                    li.setAttribute('data-value', options_to_show[i]);
                    li.addEventListener("click", selectOption);
                    autocomplete_list.appendChild(li);
                }
            } else {
                const li = document.createElement("li");
                li.classList.add("not-cursor");
                li.innerText = "No options found";
                autocomplete_list.appendChild(li);
            }
        }


        // Listener to autocomplete results when clicked set the selected property in the select option
        function selectOption(e) {
            const wrapper = e.target.parentNode.parentNode.parentNode;
            const input_search = wrapper.querySelector(".selected-input");
            const option = wrapper.querySelector(`select option[value="${e.target.dataset.value}"]`);

            option.setAttribute("selected", "");
            createToken(wrapper, e.target.dataset.value);
            if (input_search.value) {
                input_search.value = "";
            }

            input_search.focus();

            e.target.remove();
            const autocomplete_list = wrapper.querySelector(".autocomplete-list");


            if (!autocomplete_list.children.length) {
                const li = document.createElement("li");
                li.classList.add("not-cursor");
                li.innerText = "No options found";
                autocomplete_list.appendChild(li);
            }

            const event = new Event('keyup');
            input_search.dispatchEvent(event);
            e.stopPropagation();
        }


        // function that returns a list with the autcomplete list of matches
        function autocomplete(query, options) {
            // No query passed, just return entire list
            if (!query) {
                return options;
            }
            let options_return = [];

            for (let i = 0; i < options.length; i++) {
                if (query.toLowerCase() === options[i].slice(0, query.length).toLowerCase()) {
                    options_return.push(options[i]);
                }
            }
            return options_return;
        }


        // Returns the options that are selected by the user and the ones that are not
        function getOptions(select) {
            // Select all the options available
            const all_options = Array.from(
                select.querySelectorAll("option")
            ).map(el => el.value);

            // Get the options that are selected from the user
            const options_selected = Array.from(
                select.querySelectorAll("option:checked")
            ).map(el => el.value);

            // Create an autocomplete options array with the options that are not selected by the user
            const autocomplete_options = [];
            all_options.forEach(option => {
                if (!options_selected.includes(option)) {
                    autocomplete_options.push(option);
                }
            });

            autocomplete_options.sort();

            return {
                options_selected,
                autocomplete_options
            };

        }

        // Listener for when the user wants to remove a given token.
        function removeToken(e) {
            // Get the value to remove
            const value_to_remove = e.target.dataset.option;
            const wrapper = e.target.parentNode.parentNode;
            const input_search = wrapper.querySelector(".selected-input");
            const dropdown = wrapper.querySelector(".dropdown-icon");
            // Get the options in the select to be unselected
            const option_to_unselect = wrapper.querySelector(`select option[value="${value_to_remove}"]`);
            option_to_unselect.removeAttribute("selected");
            // Remove token attribute
            e.target.parentNode.remove();
            input_search.focus();
            dropdown.classList.remove("active");
            const event = new Event('click');
            dropdown.dispatchEvent(event);
            e.stopPropagation();
        }

        // Listen for 2 sequence of hits on the delete key, if this happens delete the last token if exist
        function deletePressed(e) {
            const wrapper = e.target.parentNode.parentNode;
            const input_search = e.target;
            const key = e.keyCode || e.charCode;
            const tokens = wrapper.querySelectorAll(".selected-wrapper");

            if (tokens.length) {
                const last_token_x = tokens[tokens.length - 1].querySelector("a");
                let hits = +last_token_x.dataset.hits;

                if (key == 8 || key == 46) {
                    if (!input_search.value) {

                        if (hits > 1) {
                            // Trigger delete event
                            const event = new Event('click');
                            last_token_x.dispatchEvent(event);
                        } else {
                            last_token_x.dataset.hits = 2;
                        }
                    }
                } else {
                    last_token_x.dataset.hits = 0;
                }
            }
            return true;
        }

        // You can call this function if you want to add new options to the select plugin
        // Target needs to be a unique identifier from the select you want to append new option for example #multi-select-plugin
        // Example of usage addOption("#multi-select-plugin", "tesla", "Tesla")
        function addOption(target, val, text) {
            const select = document.querySelector(target);
            let opt = document.createElement('option');
            opt.value = val;
            opt.innerHTML = text;
            select.appendChild(opt);
        }

        document.addEventListener("DOMContentLoaded", () => {

            // get select that has the options available
            const select = document.querySelectorAll("[data-multi-select-plugin]");
            select.forEach(select => {

                init(select);
            });

            // Dismiss on outside click
            document.addEventListener('click', () => {
                // get select that has the options available
                const select = document.querySelectorAll("[data-multi-select-plugin]");
                for (let i = 0; i < select.length; i++) {
                    if (event) {
                        var isClickInside = select[i].parentElement.parentElement.contains(event.target);

                        if (!isClickInside) {
                            const wrapper = select[i].parentElement.parentElement;
                            const dropdown = wrapper.querySelector(".dropdown-icon");
                            const autocomplete_list = wrapper.querySelector(".autocomplete-list");
                            //the click was outside the specifiedElement, do something
                            dropdown.classList.remove("active");
                            autocomplete_list.innerHTML = "";
                            addPlaceholder(wrapper);
                        }
                    }
                }
            });

        });
    </script>
@endsection
