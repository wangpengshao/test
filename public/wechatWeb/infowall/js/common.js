function toast(text, time) {
    console.log(2)
    if (!document.getElementById('toast')) {
        let _time = time || 1000;
        let parent = document.createElement('div');
        parent.setAttribute('id', 'toast');
        let child = document.createElement('p');
        child.setAttribute('class', 'text');
        let _text = document.createTextNode(text);
        child.appendChild(_text);
        parent.appendChild(child);
        document.body.appendChild(parent);
        setTimeout(function() {
            document.body.removeChild(parent);
        }, _time)
    };
}