function pa_publish(id, draftId) {
    if (!confirm('Publish this package? This cannont be undone!')) {
        return
    }

    Craft.sendActionRequest("POST", 'package/publish/publish', {data: {elementId: id, draftId: draftId}})
        .then((response) => {
            Craft.cp.displayNotice(response.data.message, response.data.notificationSettings)

            pa_message('pa-notice', response.data.notice)
            pa_message('pa-error', response.data.error)

        })
        .catch((error) => {
            Craft.cp.displayError(error.response.data.message)
        })
}

function pa_message(id, text) {
    element = document.getElementById(id)
    if (text) {
        element.innerHTML = text
        element.style.display = ''
    } else element.style.display = 'none'
}