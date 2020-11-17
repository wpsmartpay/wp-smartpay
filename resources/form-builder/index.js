import domReady from '@wordpress/dom-ready'
import { render, useEffect } from '@wordpress/element'
import { HashRouter, Route, Switch } from 'react-router-dom'

import { registerCoreBlocks } from '@wordpress/block-library'

import { FormList } from './pages/index'
import { CreateForm } from './pages/create'
import { EditForm } from './pages/edit'

import { NotFound } from '../js/pages/not-found'

import { registerSmartPayFormBlocks } from './blocks'

import './styles.scss'
import './store/index'

domReady(function () {
    registerCoreBlocks()

    registerSmartPayFormBlocks()

    const SmartPayForm = () => {
        return (
            <div>
                <HashRouter>
                    <Switch>
                        {/* Form */}
                        <Route exact path="/" component={FormList} />
                        <Route exact path="/create" component={CreateForm} />
                        <Route
                            exact
                            path="/:formId/edit"
                            component={EditForm}
                        />

                        {/* Not Found */}
                        <Route component={NotFound} />
                    </Switch>
                </HashRouter>
            </div>
        )
    }

    render(<SmartPayForm />, document.getElementById('smartpay-form'))
})
