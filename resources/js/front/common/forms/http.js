module.exports = {
    /**
     * Helper method for making POST HTTP requests.
     */
    post(uri, form, config = {}) {
        return Laravel.sendForm('post', uri, form, config);
    },


    /**
     * Helper method for making PUT HTTP requests.
     */
    put(uri, form, config = {}) {
        return Laravel.sendForm('put', uri, form, config);
    },


    /**
     * Helper method for making PATCH HTTP requests.
     */
    patch(uri, form, config = {}) {
        return Laravel.sendForm('patch', uri, form, config);
    },


    /**
     * Helper method for making DELETE HTTP requests.
     */
    delete(uri, form, config = {}) {
        return Laravel.sendForm('delete', uri, form, config);
    },
    /**
     * Helper method for making GET HTTP requests.
     */
    get(uri, form, config = {}) {
        return Laravel.sendForm('get', uri, form, config);
    },


    /**
     * Send the form to the back-end server.
     *
     * This function will clear old errors, update "busy" status, etc.
     */
    sendForm(method, uri, form, config = {}) {
        return new Promise((resolve, reject) => {
            form.startProcessing();

            axios[method](uri, JSON.parse(JSON.stringify(form)), config)
                .then(response => {
                    form.finishProcessing();

                    if (response.data.msg) {
                        form.setMsg(response.data.msg);
                    }

                    if (response.data.redirect) {
                        let timeout = 0;
                        if (response.data.msg) {
                            timeout = 3000;
                        }
                        setTimeout(() => {
                            location.href = response.data.redirect;
                        }, timeout);

                        return;
                    }
                    resolve(response.data);
                })
                .catch(errors => {
                    form.errors.set(errors.response.data);
                    form.busy = false;

                    reject(errors.response.data);
                });
        });
    }
};
