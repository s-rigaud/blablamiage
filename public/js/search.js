// Originally written by Samuel Rigaud the 02/04/2019 for the intern Lydia Project
// Rewritten for the PHP blablamiage project by Elyne, Gaetan and Samuel

// Display suggestions at the bottom of the search bar
function updateValues(htmlInput, jsonSuggestions, autocomplete_list) {
    let val = htmlInput.value
    deleteSuggestions(null, htmlInput, autocomplete_list)
    if (!val) {
        return
    }

    currentFocus = -1
    for (let i = 0; i < jsonSuggestions.length; i++) {
        let b = document.createElement("DIV")
        b.innerHTML = "<strong>" + jsonSuggestions[i]["name"] + "</strong>"
        b.classList.add("autocomplete-items")
        b.addEventListener("click", function(e) {
            setInputValues(htmlInput, jsonSuggestions[i])
            deleteSuggestions(null, htmlInput, autocomplete_list)
        })
        autocomplete_list.appendChild(b)
    }
}

// Set the focus on a specific suggestion
function updateFocus(x, currentFocus) {
    removeFocus(x)
    if (currentFocus >= x.children.length) {
        currentFocus = 0
    }
    if (currentFocus < 0) {
        currentFocus = (x.children.length - 1)
    }
    x.children[currentFocus].classList.add("autocomplete-active")
}

//Remove the focus of every element of the list
function removeFocus(x) {
    for (let child of x.children) {
        child.classList.remove("autocomplete-active")
    }
}

//Close the suggestion list
function deleteSuggestions(elmnt, htmlInput, autocomplete_list) {
    let children = Array.from(autocomplete_list.children);
    const child_count = children.length
        // Index necessary for looping deletion
    for (let i = 0; i < child_count; i++) {
        if (children[i].classList.contains("autocomplete-items")) {
            if (!(elmnt == children[i] || elmnt == htmlInput)) {
                autocomplete_list.removeChild(children[i])
            }
        }
    }
}

function setInputValues(htmlInput, jsonCity) {
    //Value is the display value while the value attribute is the html content value
    htmlInput.setAttribute("value", jsonCity["id"])
    htmlInput.value = jsonCity["name"]
}

const capitalize = (str) => {
    if (typeof str === 'string') {
        return str.replace(/^\w/, c => c.toUpperCase());
    } else {
        return '';
    }
};

//Initialize the search bar to react to inputs
function autocomplete_setup(htmlInput, jsonSuggestions, index) {
    let currentFocus = -1
    let autocomplete_list = document.createElement("DIV")
    autocomplete_list.setAttribute("id", "autocomplete-list" + index)
    autocomplete_list.setAttribute("class", "autocomplete-list")
    htmlInput.parentNode.appendChild(autocomplete_list)

    //Replace city by actual id
    $("#city_form").submit(function(event) {
        if ($("#from_search_bar").val() && $("#to_search_bar").val() && $("#at").val()) {
            console.log('sending')
                //If user enter a good city but don't validate
            if (htmlInput.getAttribute('value') == null) {
                for (let city of jsonSuggestions) {
                    if (city['name'] == capitalize(htmlInput.value)) {
                        htmlInput.setAttribute('value', city['id'])
                    }
                }
            }
            htmlInput.value = htmlInput.getAttribute('value')
            return true;
        }
        return false
    });

    //Main logic for inputs in the input
    htmlInput.addEventListener("keydown", function(e) {
        if (e.key == "Enter") {
            if (currentFocus > -1) {
                if (typeof jsonSuggestions[currentFocus] !== "undefined") {
                    setInputValues(htmlInput, jsonSuggestions[currentFocus])
                }
                deleteSuggestions(null, htmlInput, autocomplete_list)
            }

        } else if (e.key == "ArrowDown" || e.key == "ArrowUp") {
            if (e.key == "ArrowDown") {
                currentFocus++
            } else {
                currentFocus--
            }
            if (typeof jsonSuggestions[currentFocus] !== "undefined") {
                setInputValues(htmlInput, jsonSuggestions[currentFocus])
            }
            updateFocus(autocomplete_list, currentFocus)

        } else if ((e.keyCode > 36 && e.keyCode < 91) || e.key == "Backspace") {
            //Used to potentially display on erase
            let keyPushed = e.key
                //On delete (Specific syntax due to JS "forgetting" behaviour)
                //AJAX Async requests  (get content of an other page of the site)
            searchCity = htmlInput.value
            if (e.key == "Backspace") {
                searchCity = searchCity.substring(0, htmlInput.value.length - 1)
                if (searchCity.length > 1) {
                    $(function() {
                        $.ajax({
                            url: "/city/find/" + searchCity
                        }).done(
                            function(data) {
                                jsonSuggestions = data["cities"]
                                updateValues(htmlInput, jsonSuggestions, autocomplete_list)
                            })
                    })
                }
            } else {
                if (searchCity.length > 0) {
                    $(function() {
                        $.ajax({
                            url: "/city/find/" + searchCity + keyPushed
                        }).done(
                            function(data) {
                                jsonSuggestions = data["cities"]
                                updateValues(htmlInput, jsonSuggestions, autocomplete_list)
                            })
                    })
                }
            }

        }
    })
}