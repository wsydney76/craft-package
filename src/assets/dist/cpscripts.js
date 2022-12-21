function pa_release(packageId) {
    if (!confirm('Publish this package? This cannont be undone!')) {
        return
    }


    pa_sendActionRequest('package/package/release', {
        packageId: packageId,
        options: pa_getOptions('pa-release-options')
    })

}

function pa_attachNewDrafts(packageId) {
    ids = []
    var inputs = document.getElementsByName('attach-new-drafts[]');
    inputs.forEach(
        (input) => ids.push(input.value)
    )


    pa_sendActionRequest('package/package/attach-new-drafts', {
        packageId: packageId,
        ids: ids,
        options: pa_getOptions('pa-attach-options')
    })

}

function pa_attachNewEntry(packageId) {

    pa_sendActionRequest('package/package/attach-new-entry', {
        packageId: packageId,
        options: pa_getOptions('pa-create-options')
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

function pa_getOptions(id) {
    var options = {}
    document.getElementsByName(id).forEach(
        (input) => options[input.id] = input.type === 'checkbox' ? input.checked : input.value
    )
    return options;
}