export default function projectsDirectory() {
    return {
        opener: null,
        init() {
            this.$nextTick(() => {
                if (location.hash === '#project-create' || this.$el.dataset.createErrors === 'true') this.openCreate()
            })
        },
        openCreate() {
            if (!this.$refs.createDialog || this.$refs.createDialog.open) return
            this.opener = document.activeElement
            this.$refs.createDialog.showModal()
            this.$refs.projectName.focus()
        },
        closeCreate() { this.$refs.createDialog.close() },
        restoreFocus() {
            if (location.hash === '#project-create') history.replaceState(null, '', location.pathname + location.search)
            if (this.opener instanceof HTMLElement && this.opener !== document.body) this.opener.focus()
            else this.$refs.createButton?.focus()
        },
    }
}
