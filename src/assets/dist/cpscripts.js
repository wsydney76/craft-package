function pa_release(id) {
    if (!confirm('Publish this package? This cannont be undone!')) {
        return
    }

    var options = {}
    document.getElementsByName('pa-release-options').forEach(
        (input) => options[input.id] = input.checked
    )

    pa_sendActionRequest('package/package/release', {
        packageId: packageId,
        options: options
    })

}

function pa_attachNewDrafts(packageId) {
    ids = []
    var inputs = document.getElementsByName('attach-new-drafts[]');
    inputs.forEach(
        (input) => ids.push(input.value)
    )

    var options = {}
    document.getElementsByName('pa-attach-options').forEach(
        (input) => options[input.id] = input.checked
    )

    pa_sendActionRequest('package/package/attach-new-drafts', {
        packageId: packageId,
        ids: ids,
        options: options
    })

}

function pa_attachNewEntry(packageId) {

    var options = {}
    document.getElementsByName('pa-create-options').forEach(
        (input) => options[input.id] = input.value
    )

    pa_sendActionRequest('package/package/attach-new-entry', {
        packageId: packageId,
        options: options
    })

}

function pa_sendActionRequest(action, data) {
    Craft.sendActionRequest("POST", action,
            {
                data: data
            })
        .then((response) => {
            Craft.cp.displayNotice(response.data.message, response.data.notificationSettings)

            pa_message('pa-notice', response.data.notice)
            pa_message('pa-error', response.data.error)

            co_getSectionHtml('package-0-0-0');

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
    } else {
        element.style.display = 'none'
    }
}