import React, { Component } from 'react';
import { Link } from 'react-router';
import SearchBar from './SearchBar';

export default class Main extends Component {
  constructor() {
    super();
    this.state = {
      shouldSearch: false,
    };
  }

  render() {
    const children = React.Children.map(this.props.children, (child) => {
      const props = {
        shouldSearch: this.state.shouldSearch,
        unsetShouldSearch: this.unsetShouldSearch.bind(this),
      };
      return React.cloneElement(child, props);
    });

    return (
        <div className="inner-search-container">
          <SearchBar
            onSearch={this.doSearch.bind(this)}
            location={this.props.location}
          />

          <div className="search-result-container">
            {children}
          </div>
          <div className="is-searching">
            <div className="progress-spinner">
              <i className="fa fa-spinner fa-pulse"></i>
            </div>
          </div>
		  <div id="overlay-container"></div>
		</div>
    );
  }

  unsetShouldSearch() {
    this.setState({ shouldSearch: false });
  }

  doSearch(searchKeyword, query) {
    const base_uri = site_base_uri;
    const queries = [];
    if (searchKeyword) {
      queries.push(`q=${searchKeyword}`);
    }
    for (const field in query) {
      const values = query[field].join(',');
      if (values) {
        queries.push(`${field}=[${values}]`);
      }
    }
    const queryString = queries.join('&');

    this.setState({ shouldSearch: true });
    this.context.router.push(`${base_uri}/search?${queryString}`);
  }
}

Main.contextTypes = {
  router: React.PropTypes.object,
};
