<!-- Theme Settings -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="theme-settings-offcanvas">
    <div class="d-flex align-items-center bg-primary p-3 offcanvas-header">
        <h5 class="text-white m-0">Theme Settings</h5>
        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body p-0">
        <div data-simplebar class="h-100">
            <div class="card mb-0 p-3">
                <h5 class="mt-0 font-16 fw-bold mb-3">Choose Layout</h5>
                <div class="row">
                    <div class="col-4">
                        <div class="form-check card-radio">
                            <input id="customizer-layout01" name="data-layout" type="radio" value="vertical" class="form-check-input">
                            <label class="form-check-label p-0 avatar-md w-100" for="customizer-layout01">
                                <span class="d-flex h-100">
                                    <span class="flex-shrink-0">
                                        <span class="bg-light d-flex h-100 border-end flex-column p-1 px-2">
                                            <span class="d-block p-1 bg-dark-lighten rounded mb-1"></span>
                                            <span class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                            <span class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                            <span class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                            <span class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                        </span>
                                    </span>
                                    <span class="flex-grow-1">
                                        <span class="d-flex h-100 flex-column">
                                            <span class="bg-light d-block p-1"></span>
                                        </span>
                                    </span>
                                </span>
                            </label>
                        </div>
                        <h5 class="font-14 text-center text-muted mt-2">Vertical</h5>
                    </div>
                    <div class="col-4">
                        <div class="form-check card-radio">
                            <input id="customizer-layout02" name="data-layout" type="radio" value="horizontal" class="form-check-input">
                            <label class="form-check-label p-0 avatar-md w-100" for="customizer-layout02">
                                <span class="d-flex h-100 flex-column">
                                    <span class="bg-light d-flex p-1 align-items-center border-bottom border-secondary border-opacity-25">
                                        <span class="d-block p-1 bg-dark-lighten rounded me-1"></span>
                                        <span class="d-block border border-3 border-secondary border-opacity-25 rounded ms-auto"></span>
                                        <span class="d-block border border-3 border-secondary border-opacity-25 rounded ms-1"></span>
                                        <span class="d-block border border-3 border-secondary border-opacity-25 rounded ms-1"></span>
                                        <span class="d-block border border-3 border-secondary border-opacity-25 rounded ms-1"></span>
                                    </span>
                                    <span class="bg-light d-block p-1"></span>
                                </span>
                            </label>
                        </div>
                        <h5 class="font-14 text-center text-muted mt-2">Horizontal</h5>
                    </div>
                </div>

                <h5 class="my-3 font-16 fw-bold">Color Scheme</h5>

                <div class="colorscheme-cardradio">
                    <div class="row">
                        <div class="col-4">
                            <div class="form-check card-radio">
                                <input class="form-check-input" type="radio" name="data-bs-theme" id="layout-color-light" value="light">
                                <label class="form-check-label p-0 avatar-md w-100" for="layout-color-light">
                                    <div id="sidebar-size">
                                        <span class="d-flex h-100">
                                            <span class="flex-shrink-0">
                                                <span class="bg-light d-flex h-100 border-end flex-column p-1 px-2">
                                                    <span class="d-block p-1 bg-dark-lighten rounded mb-1"></span>
                                                    <span class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                                    <span class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                                    <span class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                                    <span class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                                </span>
                                            </span>
                                            <span class="flex-grow-1">
                                                <span class="d-flex h-100 flex-column bg-white rounded-2">
                                                    <span class="bg-light d-block p-1"></span>
                                                </span>
                                            </span>
                                        </span>
                                    </div>

                                    <div id="topnav-color" class="bg-white rounded-2 h-100">
                                        <span class="d-flex h-100 flex-column">
                                            <span class="bg-light d-flex p-1 align-items-center border-bottom border-secondary border-opacity-25">
                                                <span class="d-block p-1 bg-dark-lighten rounded me-1"></span>
                                                <span class="d-block border border-3 border-secondary border-opacity-25 rounded ms-auto"></span>
                                                <span class="d-block border border-3 border-secondary border-opacity-25 rounded ms-1"></span>
                                                <span class="d-block border border-3 border-secondary border-opacity-25 rounded ms-1"></span>
                                                <span class="d-block border border-3 border-secondary border-opacity-25 rounded ms-1"></span>
                                            </span>
                                            <span class="d-flex h-100 flex-column bg-white rounded-2">
                                                <span class="bg-light d-block p-1"></span>
                                            </span>
                                        </span>
                                    </div>
                                </label>
                            </div>
                            <h5 class="font-14 text-center text-muted mt-2">Light</h5>
                        </div>

                        <div class="col-4">
                            <div class="form-check card-radio">
                                <input class="form-check-input" type="radio" name="data-bs-theme" id="layout-color-dark" value="dark">
                                <label class="form-check-label p-0 avatar-md w-100 bg-black" for="layout-color-dark">
                                    <div id="sidebar-size">
                                        <span class="d-flex h-100">
                                            <span class="flex-shrink-0">
                                                <span class="bg-light d-flex h-100 flex-column p-1 px-2">
                                                    <span class="d-block p-1 bg-dark-lighten rounded mb-1"></span>
                                                    <span class="d-block border border-secondary border-opacity-25 border-3 rounded w-100 mb-1"></span>
                                                    <span class="d-block border border-secondary border-opacity-25 border-3 rounded w-100 mb-1"></span>
                                                    <span class="d-block border border-secondary border-opacity-25 border-3 rounded w-100 mb-1"></span>
                                                    <span class="d-block border border-secondary border-opacity-25 border-3 rounded w-100 mb-1"></span>
                                                </span>
                                            </span>
                                            <span class="flex-grow-1">
                                                <span class="d-flex h-100 flex-column">
                                                    <span class="bg-light d-block p-1"></span>
                                                </span>
                                            </span>
                                        </span>
                                    </div>

                                    <div id="topnav-color">
                                        <span class="d-flex h-100 flex-column">
                                            <span class="bg-light-lighten d-flex p-1 align-items-center border-bottom border-opacity-25 border-primary border-opacity-25">
                                                <span class="d-block p-1 bg-dark-lighten rounded me-1"></span>
                                                <span class="d-block border border-primary border-opacity-25 border-3 rounded ms-auto"></span>
                                                <span class="d-block border border-primary border-opacity-25 border-3 rounded ms-1"></span>
                                                <span class="d-block border border-primary border-opacity-25 border-3 rounded ms-1"></span>
                                                <span class="d-block border border-primary border-opacity-25 border-3 rounded ms-1"></span>
                                            </span>
                                            <span class="bg-light-lighten d-block p-1"></span>
                                        </span>
                                    </div>
                                </label>
                            </div>
                            <h5 class="font-14 text-center text-muted mt-2">Dark</h5>
                        </div>
                    </div>
                </div>

                <div id="layout-width">
                    <h5 class="my-3 font-16 fw-bold">Layout Mode</h5>

                    <div class="row">
                        <div class="col-4">
                            <div class="form-check card-radio">
                                <input class="form-check-input" type="radio" name="data-layout-mode" id="layout-mode-fluid" value="fluid">
                                <label class="form-check-label p-0 avatar-md w-100" for="layout-mode-fluid">
                                    <div id="sidebar-size">
                                        <span class="d-flex h-100">
                                            <span class="flex-shrink-0">
                                                <span class="bg-light d-flex h-100 border-end flex-column p-1 px-2">
                                                    <span class="d-block p-1 bg-dark-lighten rounded mb-1"></span>
                                                    <span class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                                    <span class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                                    <span class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                                    <span class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                                </span>
                                            </span>
                                            <span class="flex-grow-1">
                                                <span class="d-flex h-100 flex-column rounded-2">
                                                    <span class="bg-light d-block p-1"></span>
                                                </span>
                                            </span>
                                        </span>
                                    </div>

                                    <div id="topnav-color">
                                        <span class="d-flex h-100 flex-column">
                                            <span class="bg-light d-flex p-1 align-items-center border-bottom border-secondary border-opacity-25">
                                                <span class="d-block p-1 bg-dark-lighten rounded me-1"></span>
                                                <span class="d-block border border-3 border-secondary border-opacity-25 rounded ms-auto"></span>
                                                <span class="d-block border border-3 border-secondary border-opacity-25 rounded ms-1"></span>
                                                <span class="d-block border border-3 border-secondary border-opacity-25 rounded ms-1"></span>
                                                <span class="d-block border border-3 border-secondary border-opacity-25 rounded ms-1"></span>
                                            </span>
                                            <span class="bg-light d-block p-1"></span>
                                        </span>
                                    </div>
                                </label>
                            </div>
                            <h5 class="font-14 text-center text-muted mt-2">Fluid</h5>
                        </div>
                        <div class="col-4" id="layout-boxed">
                            <div class="form-check card-radio">
                                <input class="form-check-input" type="radio" name="data-layout-mode" id="layout-mode-boxed" value="boxed">
                                <label class="form-check-label p-0 avatar-md w-100 px-2" for="layout-mode-boxed">
                                    <div id="sidebar-size" class="border-start border-end">
                                        <span class="d-flex h-100">
                                            <span class="flex-shrink-0">
                                                <span class="bg-light d-flex h-100 border-end flex-column p-1 px-2">
                                                    <span class="d-block p-1 bg-dark-lighten rounded mb-1"></span>
                                                    <span class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                                    <span class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                                    <span class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                                    <span class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                                </span>
                                            </span>
                                            <span class="flex-grow-1">
                                                <span class="d-flex h-100 flex-column rounded-2">
                                                    <span class="bg-light d-block p-1"></span>
                                                </span>
                                            </span>
                                        </span>
                                    </div>

                                    <div id="topnav-color" class="border-start border-end h-100">
                                        <span class="d-flex h-100 flex-column">
                                            <span class="bg-light d-flex p-1 align-items-center border-bottom border-secondary border-opacity-25">
                                                <span class="d-block p-1 bg-dark-lighten rounded me-1"></span>
                                                <span class="d-block border border-3 border-secondary border-opacity-25 rounded ms-auto"></span>
                                                <span class="d-block border border-3 border-secondary border-opacity-25 rounded ms-1"></span>
                                                <span class="d-block border border-3 border-secondary border-opacity-25 rounded ms-1"></span>
                                                <span class="d-block border border-3 border-secondary border-opacity-25 rounded ms-1"></span>
                                            </span>
                                            <span class="bg-light d-block p-1"></span>
                                        </span>
                                    </div>
                                </label>
                            </div>
                            <h5 class="font-14 text-center text-muted mt-2">Boxed</h5>
                        </div>

                        <div class="col-4" id="layout-detached">
                            <div class="form-check sidebar-setting card-radio">
                                <input class="form-check-input" type="radio" name="data-layout-mode" id="data-layout-detached" value="detached">
                                <label class="form-check-label p-0 avatar-md w-100" for="data-layout-detached">
                                    <span class="d-flex h-100 flex-column">
                                        <span class="bg-light d-flex p-1 align-items-center border-bottom ">
                                            <span class="d-block p-1 bg-dark-lighten rounded me-1"></span>
                                            <span class="d-block border border-3 border-secondary border-opacity-25 rounded ms-auto"></span>
                                            <span class="d-block border border-3 border-secondary border-opacity-25 rounded ms-1"></span>
                                            <span class="d-block border border-3 border-secondary border-opacity-25 rounded ms-1"></span>
                                            <span class="d-block border border-3 border-secondary border-opacity-25 rounded ms-1"></span>
                                        </span>
                                        <span class="d-flex h-100 p-1 px-2">
                                            <span class="flex-shrink-0">
                                                <span class="bg-light d-flex h-100 flex-column p-1 px-2">
                                                    <span class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                                    <span class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                                    <span class="d-block border border-3 border-secondary border-opacity-25 rounded w-100"></span>
                                                </span>
                                            </span>
                                        </span>
                                        <span class="bg-light d-block p-1 mt-auto px-2"></span>
                                    </span>

                                </label>
                            </div>
                            <h5 class="font-14 text-center text-muted mt-2">Detached</h5>
                        </div>
                    </div>
                </div>

                <h5 class="my-3 font-16 fw-bold">Topbar Color</h5>

                <div class="row">
                    <div class="col-4">
                        <div class="form-check card-radio">
                            <input class="form-check-input" type="radio" name="data-topbar-color" id="topbar-color-light" value="light">
                            <label class="form-check-label p-0 avatar-md w-100" for="topbar-color-light">
                                <div id="sidebar-size">
                                    <span class="d-flex h-100">
                                        <span class="flex-shrink-0">
                                            <span class="bg-light d-flex h-100 border-end  flex-column p-1 px-2">
                                                <span class="d-block p-1 bg-dark-lighten rounded mb-1"></span>
                                                <span class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                                <span class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                                <span class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                                <span class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                            </span>
                                        </span>
                                        <span class="flex-grow-1">
                                            <span class="d-flex h-100 flex-column">
                                                <span class="bg-light d-block p-1"></span>
                                            </span>
                                        </span>
                                    </span>
                                </div>

                                <div id="topnav-color">
                                    <span class="d-flex h-100 flex-column">
                                        <span class="bg-light d-flex p-1 align-items-center border-bottom border-secondary border-opacity-25">
                                            <span class="d-block p-1 bg-dark-lighten rounded me-1"></span>
                                            <span class="d-block border border-3 border-secondary border-opacity-25 rounded ms-auto"></span>
                                            <span class="d-block border border-3 border-secondary border-opacity-25 rounded ms-1"></span>
                                            <span class="d-block border border-3 border-secondary border-opacity-25 rounded ms-1"></span>
                                            <span class="d-block border border-3 border-secondary border-opacity-25 rounded ms-1"></span>
                                        </span>
                                        <