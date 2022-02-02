// keycodes with ctrl key
var keycodes = {
    13: submitform, // ctrl + enter
    163: comment, // ctrl + # (firefox)
    191: comment, // ctrl + # (chrome)
} // ctrl + y

function pressed(e) {
    let keycode = window.event ? event.keyCode : e.which
    if (e.ctrlKey) {
        if (keycodes[keycode] !== undefined) {
            keycodes[keycode]();
        }
    }
}

function submitform() {
    document.forms[0].submit();
}

function comment(selector = '#codearea') {
    // get the current selection
    let selstart = document.querySelector(selector).selectionStart;
    let selend = document.querySelector(selector).selectionEnd;

    let comment_lines = getLinesForComment(selector, selstart, selend);
    let txtValue = document.querySelector(selector).value.split("\n");
    let newTxtVal = txtValue.slice(comment_lines[0], comment_lines[1] + 1)

    // loop through lines and toggle comment for each
    newTxtVal.forEach(function (line, index) {
        newTxtVal[index] = toggleComment(line)
    });

    // join all the lines to one array
    let newLines = txtValue.slice(0, comment_lines[0])
        .concat(newTxtVal)
        .concat(txtValue.slice(comment_lines[1] + 1));

    // comment the values
    document.querySelector(selector).value = newLines.join("\n");
}

function toggleComment(line) {
    // if line starts with "--", remove it, else put it
    // if it is empty do nothing
    if (line.match(/^$/)) {
        return line
    } else if (line.match(/^-- /)) {
        return line.substring(3);
    } else {
        return "-- " + line;
    }
}

function getLinesForComment(selector, selstart, selend) {
    let txt = document.querySelector(selector).value;

    let comment_start = lineFromIndex(txt, selstart);
    let comment_end = lineFromIndex(txt, selend);

    return [comment_start, comment_end]
}

function lineFromIndex(txt, idx) {
    // line where index is found is equal to the number of '\n' before the index
    let txtbefore = txt.substring(0, idx);
    return (txtbefore.match(/\n/g) || []).length;
}