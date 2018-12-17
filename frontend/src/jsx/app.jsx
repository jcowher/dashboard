import React, {Component} from 'react';
import ReactDOM from 'react-dom';
import {CircularProgress, CssBaseline} from '@material-ui/core';
import {MuiThemeProvider, createMuiTheme} from '@material-ui/core/styles';
import moment from 'moment';
import ReminderList from './reminder-list.jsx';
import BottomAppBar from './bottom-app-bar.jsx';

const theme = createMuiTheme({
    typography: {
        useNextVariants: true
    }
});

const styles = {
    progress: {
        position: 'absolute',
        top: '50%',
        left: '50%',
        transform: 'translate(-50%, -50%)'
    }
};

class App extends Component {
    state = {
        error: null,
        items: [],
        isLoaded: false
    };

    componentDidMount() {
        let startDate = moment().format('YYYY-MM-DD');
        let endDate = moment().add(6, 'months').format('YYYY-MM-DD');
        fetch("//api.dashboard.docksal/reminder/" + startDate + "/" + endDate)
            .then(res => res.json())
            .then(
                (response) => {
                    this.setState({
                        isLoaded: true,
                        items: response
                    });
                },
                (error) => {
                    this.setState({
                        isLoaded: true,
                        error
                    })
                })
        ;
    }

    render() {
        const {error, isLoaded, items} = this.state;

        if (error) {
            return <div>Error: {error.message}</div>
        } else if (!isLoaded) {
            return <CircularProgress style={{...styles.progress}}/>
        } else {
            return (
                <MuiThemeProvider theme={theme}>
                    <CssBaseline/>
                    <ReminderList items={items}/>
                    <BottomAppBar/>
                </MuiThemeProvider>
            )
        }
    }
}

ReactDOM.render(<App/>, document.getElementById('app'));
