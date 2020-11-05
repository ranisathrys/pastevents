jQuery(document).ready(function ($) {
  //LIKE
  $(".pe-like-link").click(function (e) {
    e.preventDefault();
    post_id = $(this).attr("data-post_id");
    nonce = $(this).attr("data-nonce");
    $.ajax({
      type: "post",
      dataType: "json",
      url: myAjax.ajaxurl,
      data: { action: "event_like", post_id: post_id, nonce: nonce },
      success: function (response) {
        if (response.type == "success") {
          $("#like_counter").html(response.like_count);
        } else {
          alert("Your like could not be added");
        }
      },
    });
  });

  //META
  var res = "";

  jQuery.ajax({
    url: metaAjax.ajaxurl,
    type: "post",
    dataType: "json",
    data: { action: "meta_ajax" },
    success: function (response) {
      var res = Object.entries(response);
      Calendar3(
        "calendar3",
        new Date().getFullYear(),
        new Date().getMonth(),
        res
      );

      if ($("#calendar3").length) {
        document.querySelector("#calendar3").onchange = function Kalendar3() {
          Calendar3(
            "calendar3",
            document.querySelector("#calendar3 input").value,
            parseFloat(
              document.querySelector("#calendar3 select").options[
                document.querySelector("#calendar3 select").selectedIndex
              ].value
            ),
            res
          );
        };
      }
    },
  });

  function Calendar3(id, year, month, res) {
    if (!$("#calendar3").length) {
      return;
    }

    var res = res;
    var Dlast = new Date(year, month + 1, 0).getDate(),
      D = new Date(year, month, Dlast),
      WDlast = D.getDay(),
      WDfirst = new Date(D.getFullYear(), D.getMonth(), 1).getDay(),
      calendar = "<tr>",
      month = document.querySelector(
        "#" + id + ' option[value="' + D.getMonth() + '"]'
      ),
      year = document.querySelector("#" + id + " input");
    if (WDfirst != 0) {
      for (var i = 1; i < WDfirst; i++) calendar += "<td>";
    } else {
      for (var i = 0; i < 6; i++) calendar += "<td>";
    }
    for (var i = 1; i <= Dlast; i++) {
      if (
        i == new Date().getDate() &&
        D.getFullYear() == new Date().getFullYear() &&
        D.getMonth() == new Date().getMonth()
      ) {
        calendar += '<td class="today">' + i;
      } else {
        var match = 0;
        var current_stamp = "";
        var mon = D.getMonth() + 1;
        if (mon < 10 && i < 10) {
          current_stamp = D.getFullYear() + "-0" + mon + "-0" + i;
        } else if (i < 10) {
          current_stamp = D.getFullYear() + "-" + mon + "-0" + i;
        } else if (mon < 10) {
          current_stamp = D.getFullYear() + "-0" + mon + "-" + i;
        } else {
          current_stamp = D.getFullYear() + "-" + mon + "-" + i;
        }

        var query_stamp = "";

        for (var e of res) {
          if (e[1][0][0] == current_stamp) {
            match++;
            query_stamp = current_stamp;
          }
        }

        if (match) {
          calendar +=
            '<td><a href="' +
            metaAjax.archLink +
            "?stamp=" +
            query_stamp +
            '">' +
            i +
            "<a/></td>";
        } else {
          calendar += "<td>" + i + "</td>";
        }
      }
      if (new Date(D.getFullYear(), D.getMonth(), i).getDay() == 0) {
        calendar += "<tr>";
      }
    }
    for (var i = WDlast; i < 7; i++) calendar += "<td>&nbsp;";
    document.querySelector("#" + id + " tbody").innerHTML = calendar;
    year.value = D.getFullYear();
    month.selected = true;
    if (document.querySelectorAll("#" + id + " tbody tr").length < 6) {
      document.querySelector("#" + id + " tbody").innerHTML +=
        "<tr><td>&nbsp;<td>&nbsp;<td>&nbsp;<td>&nbsp;<td>&nbsp;<td>&nbsp;<td>&nbsp;";
    }
    document.querySelector(
      "#" + id + ' option[value="' + new Date().getMonth() + '"]'
    ).style.color = "rgb(220, 0, 0)"; // current month
  }

  //LOAD MORE
  var ppp = 4; // Posts per page
  var pageNumber = 1;

  function load_posts() {
    pageNumber++;
    var str =
      "&pageNumber=" + pageNumber + "&ppp=" + ppp + "&action=more_post_ajax";
    $.ajax({
      type: "post",
      dataType: "html",
      url: loadMore.ajaxurl,
      data: str,
      success: function (data) {
        var res = data;
        if (res.length) {
          $("#ajax-posts").append(res);
          $("#more_posts").attr("disabled", false);
        } else {
          $("#more_posts").attr("disabled", true);
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        $loader.html(jqXHR + " :: " + textStatus + " :: " + errorThrown);
      },
    });
    return false;
  }

  $("#more_posts").on("click", function (e) {
    e.preventDefault();
    $("#more_posts").attr("disabled", true); // Disable the button, temp
    load_posts();
    $(this).insertAfter("#ajax-posts"); // Move the 'Load More' button to the end
  });

  /* SEARCH */
  class Search {
    constructor() {
      this.resultsDiv = $("#overlay-results");
      this.openButton = $(".search-open");
      this.closeButton = $(".search-close");
      this.searchOverlay = $(".search-overlay");
      this.searchInput = $("#search-term");
      this.submitFilters = $('#filter-submit');
      this.typingTimer;
      this.events();
    }

    events() {
      this.openButton.on("click", this.openOverlay.bind(this));
      this.closeButton.on("click", this.closeOverlay.bind(this));

      this.searchInput.on("input", this.typing.bind(this));
      this.submitFilters.on('click', function(e) {e.preventDefault()});
      this.submitFilters.on('click', this.getResults.bind(this));
    }

    openOverlay() {
      this.searchOverlay.addClass("search-overlay-active");
      $("body").addClass("noscroll");
      this.searchInput.focus();
    }

    closeOverlay() {
      this.searchOverlay.removeClass("search-overlay-active");
      $("body").removeClass("noscroll");
      this.searchInput.val('');
      this.resultsDiv.html('');
    }

    typing() {
      clearTimeout(this.typingTimer);
      this.typingTimer = setTimeout(this.getResults.bind(this), 300);
    }

    getResults() {
      // date
      let fromDate = new Date($('#date-from').val());
      let toDate = new Date($('#date-to').val());

      // concatenate date to get appropriate format 'y-m-d' for future comparison with meta values in db
      if(fromDate != 'Invalid Date') {
        fromDate = fromDate.getFullYear() + '-' + String(fromDate.getMonth() + 1) + '-' + fromDate.getDate();
        toDate = toDate.getFullYear() + '-' + String(toDate.getMonth() + 1) + '-' + toDate.getDate();
      } else {
        fromDate = 0;
        toDate = 0;
      }

      // importance
      // create an object that contains values of checked inputs
      let impArr = {
        terms: []
      };
      $(".importance").each(function(){
        if(this.checked) {
          impArr.terms.push(parseInt(this.value));
        }
      });

      // encode the importance object to send it as a parameter
      if(impArr.terms.length) {
        impArr = encodeURIComponent(JSON.stringify(impArr));
      } else {
        impArr = 0;
      }

      // fetch json data from the search endpoint
      if(this.searchInput.val().length >= 3) {
        $.getJSON(peSearch.root + '/wp-json/pe/v1/search?term=' + this.searchInput.val() + '&toDate=' + toDate + '&fromDate=' + fromDate + '&impArr=' + impArr, data => {
          // insert search result html, map values dynamically
          this.resultsDiv.html(`
          ${data.length ? '<ul class="results-ul">' : '<p>Not found</p>'}
            ${data.map(item => `
              <li>
                <div class="result-headings">
                <h2>
                  <a id="heading-link" href="${item.permalink}">${item.title}</a>
                </h2>
                </div>
                <div class="related-posts">
                  ${item.meta.map( relPost => `
                  <a href="${relPost.relLink}">${relPost.relTitle}</a><br>
                  `).join('')}
                </div>
                ${item.person.length ? '<div class="guests">' : ''}
                  ${item.person.length ? item.person.map( person => `
                    ${person.name}<br>
                    ${person.surname}<br>
                    ${person.url}<br><br>
                  `).join('') : ''}
                  ${item.person.length ? '</div>' : ''}
              </li><hr>`).join('')}
            ${data.length ? '</ul>' : ''}`
          );
        }, () => {this.resultsDiv.html('Try again')});
      }

      // empty results when no input detected 
      if(!this.searchInput.val().length < 3) {
        this.resultsDiv.html('');
      }
    }
  }

  const search = new Search();
});