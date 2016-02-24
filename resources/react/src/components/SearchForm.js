import React from 'react';

export default class SearchForm extends React.Component {
  render() {
    return (
      <form className="form-inline">
        <div className="form-group">
          <input
            className="form-control"
            value={this.props.keyword}
            ref={(input) => {
              this.input = input;
            }}
            type="text"
          />
        </div>
        {' '}
        <button className="btn btn-primary"
          onClick={(e) => {
            e.preventDefault();
            this.props.doSearch(this.input.value);
          }}
        >
          검색
        </button>
      </form>
    );
  }
}
