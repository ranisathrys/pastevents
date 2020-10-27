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
    },
  });

  function Calendar3(id, year, month, res) {
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
});
